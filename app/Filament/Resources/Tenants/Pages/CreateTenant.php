<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['cnpj'] = sanitize($data['cnpj']);
    
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);

        if ($parentRecord = $this->getParentRecord()) {
            return $this->associateRecordWithParent($record, $parentRecord);
        }

        DB::transaction(function () use ($record, $data) {

            $record->save();
            $this->generateRolesAndPermissions($record);

            $issuer = $this->createIssuer($data, $record);

            $user = $this->createUser($data, $issuer, $record);

            $issuer->users()->attach(['user_id' => $user->id]);
            
            $role = Role::where('slug', 'admin')->where('tenant_id', $record->id)->first();

            $user->roles()->attach($role);

            //Atribui as permissões do grupo
            $permissions = $role->permissions;
            foreach ($permissions as $permission) {
                $user->permissions()->attach($permission);
            }
        });




        return $record;
    }

    protected function createUser(array $data, Model $issuer, Model $tenant)
    {
        return  User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'issuer_id' => $issuer->id,
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
            'is_email_verified' => true,
            'status' => 'active',
            'is_admin' => true,

        ]);
    }

    protected function createIssuer(array $data, Model $tenant)
    {
        return  $tenant->issuers()->create([
            'razao_social' => $data['razao_social'],
            'cnpj' => sanitize($data['cnpj']),
            'regime' => $data['regime'],
            'contribuinte_icms' => $data['contribuinte_icms'],
            'cod_municipio_ibge' => 3525904,
            'tenant_id' => $tenant->id,
        ]);
    }

    protected function generateRolesAndPermissions($tenant)
    {
        $roles = config('admin.roles');
        $permissions = config('admin.permissions');

        foreach ($permissions as $key => $valuePermission) {
            $permission = Permission::create([
                'name' => $valuePermission,
                'tenant_id' => (int) $tenant->id,
            ]);

            $permissionsCollection[] = $permission;
        }

        foreach ($roles as $key => $valueRole) {
            $role = Role::create([
                'name' => $valueRole,
                'tenant_id' => (int) $tenant->id,
            ]);

            foreach ($permissionsCollection as $key => $permission) {
                $role->permissions()->attach($permission);
            }
        }
    }
}
