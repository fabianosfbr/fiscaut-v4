<?php

namespace Tests\Feature;

use App\Livewire\IssuerSwitcher;
use App\Models\Issuer;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IssuerSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_user_issuers_correctly()
    {
        // Setup Tenant
        $tenant = Tenant::create(['name' => 'Test Tenant']);

        // Setup User
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Setup Issuers
        $issuer1 = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Company A',
            'cnpj' => '11111111111111',
            'is_enabled' => true,
        ]);

        $issuer2 = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Company B',
            'cnpj' => '22222222222222',
            'is_enabled' => true,
        ]);
        
        $issuer3 = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Company C (Disabled)',
            'cnpj' => '33333333333333',
            'is_enabled' => false,
        ]);

        $issuer4 = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Company D (Not Linked)',
            'cnpj' => '44444444444444',
            'is_enabled' => true,
        ]);

        $issuer5 = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Company E (Inactive Pivot)',
            'cnpj' => '55555555555555',
            'is_enabled' => true,
        ]);

        // Attach user to issuers
        $issuer1->users()->attach($user->id, ['active' => true]);
        $issuer2->users()->attach($user->id, ['active' => true]);
        $issuer3->users()->attach($user->id, ['active' => true]);
        $issuer5->users()->attach($user->id, ['active' => false]);

        // Act & Assert
        // Note: Filament Select options are usually rendered in a specific way. 
        // We can check if the component has the options set correctly in the Select.
        // Or we can check if the HTML contains the labels.
        
        Livewire::actingAs($user)
            ->test(IssuerSwitcher::class)
            ->assertSee('Company A')
            ->assertSee('Company B')
            ->assertDontSee('Company C (Disabled)')
            ->assertDontSee('Company D (Not Linked)')
            ->assertDontSee('Company E (Inactive Pivot)');
    }

    public function test_it_switches_issuer_successfully()
    {
        // Setup Tenant
        $tenant = Tenant::create(['name' => 'Test Tenant']);

        // Setup User
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'issuer_id' => null,
        ]);

        // Setup Issuer
        $issuer = Issuer::create([
            'tenant_id' => $tenant->id,
            'razao_social' => 'Company A',
            'cnpj' => '11111111111111',
            'is_enabled' => true,
        ]);

        $issuer->users()->attach($user->id, ['active' => true]);

        // Act
        Livewire::actingAs($user)
            ->test(IssuerSwitcher::class)
            ->set('data.issuer_id', $issuer->id);

        // Assert
        $this->assertEquals($issuer->id, $user->fresh()->issuer_id);
    }
}
