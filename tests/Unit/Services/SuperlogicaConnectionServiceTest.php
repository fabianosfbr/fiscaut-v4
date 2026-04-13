<?php

namespace Tests\Unit\Services;

use App\Exceptions\SuperlogicaConnectionException;
use App\Services\SuperlogicaConnectionService;
use App\Models\Issuer;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class SuperlogicaConnectionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_returns_success_with_valid_credentials_and_http_200(): void
    {
        $issuer = $this->createIssuerWithCredentials();

        Http::fake([
            '*' => Http::response(['status' => 'ok'], 200),
        ]);

        $service = app(SuperlogicaConnectionService::class);
        $result = $service->validateConnection($issuer);

        $this->assertIsArray($result);
        $this->assertSame('ok', $result['status']);
    }

    public function test_throws_exception_when_app_token_is_missing(): void
    {
        $issuer = $this->createIssuerWithCredentials([
            'superlogica_app_token' => null,
        ]);

        $service = app(SuperlogicaConnectionService::class);

        $this->expectException(SuperlogicaConnectionException::class);
        $this->expectExceptionMessage('app_token');
        $service->validateConnection($issuer);
    }

    public function test_throws_exception_when_access_token_is_missing(): void
    {
        $issuer = $this->createIssuerWithCredentials([
            'superlogica_access_token' => '',
        ]);

        $service = app(SuperlogicaConnectionService::class);

        $this->expectException(SuperlogicaConnectionException::class);
        $this->expectExceptionMessage('access_token');
        $service->validateConnection($issuer);
    }

    public function test_throws_exception_when_base_url_is_missing(): void
    {
        $issuer = $this->createIssuerWithCredentials([
            'superlogica_base_url' => '',
        ]);

        $service = app(SuperlogicaConnectionService::class);

        $this->expectException(SuperlogicaConnectionException::class);
        $this->expectExceptionMessage('URL base');
        $service->validateConnection($issuer);
    }

    public function test_throws_exception_on_http_4xx_or_5xx(): void
    {
        $issuer = $this->createIssuerWithCredentials();

        Http::fake([
            '*' => Http::response(['error' => 'Internal'], 500),
        ]);

        $service = app(SuperlogicaConnectionService::class);

        $this->expectException(SuperlogicaConnectionException::class);
        $this->expectExceptionMessage('HTTP 500');
        $service->validateConnection($issuer);
    }

    public function test_sends_required_headers_app_token_and_access_token(): void
    {
        $issuer = $this->createIssuerWithCredentials();
        $expectedAppToken = 'app-token-test';
        $expectedAccessToken = 'access-token-test';

        Http::fake([
            '*' => Http::response(['status' => 'ok'], 200),
        ]);

        $service = app(SuperlogicaConnectionService::class);
        $service->validateConnection($issuer);

        Http::assertSent(function ($request) use ($expectedAppToken, $expectedAccessToken) {
            return ($request->header('app_token')[0] ?? null) === $expectedAppToken
                && ($request->header('access_token')[0] ?? null) === $expectedAccessToken
                && ($request->header('Content-Type')[0] ?? null) === 'application/json';
        });
    }

    /**
     * @return Issuer
     */
    private function createIssuerWithCredentials(array $tenantOverrides = []): Issuer
    {
        $tenant = (object) array_merge([
            'id' => 1,
            'name' => 'Tenant Teste',
            'superlogica_base_url' => 'https://api.superlogica.com',
            'superlogica_app_token' => 'app-token-test',
            'superlogica_access_token' => 'access-token-test',
        ], $tenantOverrides);

        $relation = Mockery::mock();
        $relation->shouldReceive('first')->andReturn($tenant);

        return new class($relation) extends Issuer
        {
            public function __construct(private ?object $relation = null)
            {
                parent::__construct();
                if ($this->relation) {
                    $this->id = 999;
                }
            }

            public function tenant(): object
            {
                return $this->relation;
            }
        };
    }
}
