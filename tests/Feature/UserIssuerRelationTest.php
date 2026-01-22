<?php

namespace Tests\Feature;

use App\Models\Issuer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\IssuerUserPermission;

class UserIssuerRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_attached_to_issuer()
    {
        // Create Tenant
        $tenant = Tenant::create(['name' => 'Test Tenant']);

        // Create User
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create Issuer
        $issuer = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Test Company',
            'cnpj' => '12345678901234',
            'ambiente' => 2,
        ]);

        // Attach User to Issuer (simulating the logic, or using the relationship directly)
        $issuer->users()->attach($user->id, ['active' => true]);

        // Assert relationship
        $this->assertTrue($user->issuers->contains($issuer));
        $this->assertTrue($issuer->users->contains($user));
        
        // Assert Pivot data
        $permission = IssuerUserPermission::where('user_id', $user->id)
            ->where('issuer_id', $issuer->id)
            ->first();
            
        $this->assertNotNull($permission);
        $this->assertTrue($permission->active);
    }

    public function test_user_can_be_detached_from_issuer()
    {
        // Create Tenant
        $tenant = Tenant::create(['name' => 'Test Tenant']);

        // Create User
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        // Create Issuer
        $issuer = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Test Company',
            'cnpj' => '12345678901234',
            'ambiente' => 2,
        ]);

        // Attach
        $issuer->users()->attach($user->id, ['active' => true]);

        // Detach
        $issuer->users()->detach($user->id);

        // Refresh models
        $user->refresh();
        $issuer->refresh();

        // Assert detached
        $this->assertFalse($user->issuers->contains($issuer));
        $this->assertFalse($issuer->users->contains($user));
    }
}
