<?php

namespace Tests\Feature\Filament;

use App\Exceptions\SuperlogicaConnectionException;
use App\Filament\Clusters\Settings\Pages\SuperlogicaCredential;
use App\Models\Issuer;
use App\Services\SuperlogicaConnectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class SuperlogicaCredentialPageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_page_loads_existing_tenant_credentials(): void
    {
        [$user] = $this->createAuthenticatedContext([
            'superlogica_base_url' => 'https://api.superlogica.com',
            'superlogica_app_token' => 'existing-app-token',
            'superlogica_access_token' => 'existing-access-token',
        ]);

        Auth::shouldReceive('user')->andReturn($user);

        Livewire::test(SuperlogicaCredential::class)
            ->assertSet('data.superlogica_base_url', 'https://api.superlogica.com')
            ->assertSet('data.superlogica_app_token', 'existing-app-token')
            ->assertSet('data.superlogica_access_token', 'existing-access-token');
    }

    public function test_save_updates_tenant_credentials(): void
    {
        [$user, $tenant] = $this->createAuthenticatedContext();
        Auth::shouldReceive('user')->andReturn($user);

        Livewire::test(SuperlogicaCredential::class)
            ->set('data.superlogica_base_url', 'https://new.superlogica.com')
            ->set('data.superlogica_app_token', 'new-app-token')
            ->set('data.superlogica_access_token', 'new-access-token')
            ->call('save');

        $this->assertSame('https://new.superlogica.com', $tenant->attributes['superlogica_base_url'] ?? null);
        $this->assertSame('new-app-token', $tenant->attributes['superlogica_app_token'] ?? null);
        $this->assertSame('new-access-token', $tenant->attributes['superlogica_access_token'] ?? null);
    }

    public function test_test_connection_calls_service_and_handles_success(): void
    {
        [$user, $tenant, $issuer] = $this->createAuthenticatedContext();
        Auth::shouldReceive('user')->andReturn($user);

        $service = Mockery::mock(SuperlogicaConnectionService::class);
        $service->shouldReceive('validateConnection')
            ->once()
            ->withArgs(fn (Issuer $arg): bool => $arg->id === $issuer->id)
            ->andReturn(['status' => 'ok']);

        $this->app->instance(SuperlogicaConnectionService::class, $service);

        Livewire::test(SuperlogicaCredential::class)->call('testConnection');

        $this->assertNotNull($tenant->id);
    }

    public function test_test_connection_handles_service_exception_with_user_friendly_flow(): void
    {
        [$user, $tenant, $issuer] = $this->createAuthenticatedContext();
        Auth::shouldReceive('user')->andReturn($user);

        $service = Mockery::mock(SuperlogicaConnectionService::class);
        $service->shouldReceive('validateConnection')
            ->once()
            ->withArgs(fn (Issuer $arg): bool => $arg->id === $issuer->id)
            ->andThrow(new SuperlogicaConnectionException('Erro de validação'));

        $this->app->instance(SuperlogicaConnectionService::class, $service);

        Livewire::test(SuperlogicaCredential::class)->call('testConnection');

        $this->assertNotNull($tenant->id);
    }

    /**
     * @return array{0: object, 1: object, 2: Issuer}
     */
    private function createAuthenticatedContext(array $tenantOverrides = []): array
    {
        $tenant = new class(array_merge([
            'id' => 1,
            'name' => 'Tenant Teste',
            'superlogica_base_url' => null,
            'superlogica_app_token' => null,
            'superlogica_access_token' => null,
        ], $tenantOverrides)) {
            public array $attributes;

            public int $id = 1;

            public function __construct(array $attributes)
            {
                $this->attributes = $attributes;
                $this->id = (int) ($attributes['id'] ?? 1);
            }

            public function attributesToArray(): array
            {
                return $this->attributes;
            }

            public function update(array $data): bool
            {
                $this->attributes = array_merge($this->attributes, $data);

                return true;
            }
        };

        $issuer = new Issuer;
        $issuer->id = 10;

        $user = new class($tenant, $issuer) {
            public Issuer $currentIssuer;

            public function __construct(private object $tenant, Issuer $issuer)
            {
                $this->currentIssuer = $issuer;
            }

            public function tenant(): object
            {
                return new class($this->tenant) {
                    public function __construct(private object $tenant) {}

                    public function first(): object
                    {
                        return $this->tenant;
                    }
                };
            }
        };

        return [$user, $tenant, $issuer];
    }
}
