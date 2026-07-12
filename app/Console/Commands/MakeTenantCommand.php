<?php

namespace App\Console\Commands;

use App\Enums\RegimesEmpresariaisEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MakeTenantCommand extends Command
{
    protected $signature = 'make:tenant';

    protected $description = 'Cria um novo assinante (tenant), issuer e usuário administrador com roles e permissões';

    public function handle(): int
    {
        $this->components->info('Criação de novo assinante');

        $razaoSocial = $this->components->ask('Razão Social da empresa');

        if (blank($razaoSocial)) {
            $this->components->error('A Razão Social é obrigatória.');

            return self::FAILURE;
        }

        $cnpj = $this->components->ask('CNPJ');

        if (blank($cnpj)) {
            $this->components->error('O CNPJ é obrigatório.');

            return self::FAILURE;
        }

        $cnpjSanitized = sanitize($cnpj);

        if (Tenant::where('cnpj', $cnpjSanitized)->exists()) {
            $this->components->error('CNPJ já cadastrado para outro assinante.');

            return self::FAILURE;
        }

        $regime = $this->components->choice('Regime', ['' => 'Nenhum', ...RegimesEmpresariaisEnum::toArray()], default: '');
        $contribuinteIcms = $this->components->confirm('Contribuinte ICMS?', default: false);

        $name = $this->components->ask('Nome do usuário administrador');

        if (blank($name)) {
            $this->components->error('O nome do usuário é obrigatório.');

            return self::FAILURE;
        }

        $email = $this->components->ask('Email do usuário administrador');

        if (blank($email)) {
            $this->components->error('O email é obrigatório.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->components->error('Email já cadastrado para outro usuário.');

            return self::FAILURE;
        }

        $password = $this->components->secret('Senha (mínimo 8 caracteres)');

        if (strlen($password ?? '') < 8) {
            $this->components->error('A senha deve ter no mínimo 8 caracteres.');

            return self::FAILURE;
        }

        $passwordConfirmation = $this->components->secret('Confirme a senha');

        if ($password !== $passwordConfirmation) {
            $this->components->error('As senhas não conferem.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($razaoSocial, $cnpjSanitized, $regime, $contribuinteIcms, $name, $email, $password, &$tenant, &$issuer, &$user) {

                $tenant = Tenant::create([
                    'razao_social' => $razaoSocial,
                    'cnpj' => $cnpjSanitized,
                    'name' => $razaoSocial,
                ]);

                $this->generateRolesAndPermissions($tenant);

                $issuer = $tenant->issuers()->create([
                    'razao_social' => $razaoSocial,
                    'cnpj' => $cnpjSanitized,
                    'regime' => $regime ?: null,
                    'contribuinte_icms' => $contribuinteIcms,
                    'cod_municipio_ibge' => 3525904,
                    'tenant_id' => $tenant->id,
                ]);

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'issuer_id' => $issuer->id,
                    'tenant_id' => $tenant->id,
                    'email_verified_at' => now(),
                    'is_email_verified' => true,
                    'status' => 'active',
                    'is_admin' => true,
                ]);

                $issuer->users()->attach(['user_id' => $user->id]);

                $role = Role::where('slug', 'super-admin')
                    ->where('tenant_id', $tenant->id)
                    ->first();

                $user->roles()->attach($role);

                $permissions = $role->permissions;
                foreach ($permissions as $permission) {
                    $user->permissions()->attach($permission);
                }
            });

            $this->components->success('Assinante criado com sucesso!');

            $rolesCount = Role::where('tenant_id', $tenant->id)->count();
            $permissionsCount = Permission::where('tenant_id', $tenant->id)->count();

            $this->components->table(
                ['Campo', 'Valor'],
                [
                    ['Tenant ID', $tenant->id],
                    ['Empresa', $razaoSocial],
                    ['CNPJ', $cnpjSanitized],
                    ['Regime', $regime ?: 'Nenhum'],
                    ['Usuário', $name],
                    ['Email', $email],
                    ['Issuer ID', $issuer->id],
                    ['Roles criadas', $rolesCount],
                    ['Permissões criadas', $permissionsCount],
                ]
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->components->error("Erro ao criar assinante: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function generateRolesAndPermissions(Tenant $tenant): void
    {
        $roles = config('admin.roles');
        $permissions = config('admin.permissions');
        $permissionsCollection = [];

        foreach ($permissions as $valuePermission) {
            $permission = Permission::create([
                'name' => $valuePermission,
                'tenant_id' => $tenant->id,
            ]);

            $permissionsCollection[] = $permission;
        }

        foreach ($roles as $valueRole) {
            $role = Role::create([
                'name' => $valueRole,
                'tenant_id' => $tenant->id,
            ]);

            foreach ($permissionsCollection as $permission) {
                $role->permissions()->attach($permission);
            }
        }
    }
}
