<?php

namespace Tests\Unit;

use App\Exceptions\FiscautConnectorException;
use App\Services\FiscautConnectorService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FiscautConnectorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Config::set('admin.fiscaconnector_url', 'https://api.fiscaconnector.com');
        Config::set('admin.fiscaconnector_api_key', 'test-api-key-12345');
    }

    public function test_sends_post_with_cgc_emp_and_sync_true(): void
    {
        Http::fake([
            '*' => function ($request) {
                $this->assertSame('12345678000199', $request->data()['cgc_emp']);
                $this->assertSame(true, $request->data()['sync']);

                return Http::response(['status' => 'OK'], 200);
            },
        ]);

        $service = new FiscautConnectorService('12345678000199');
        $result = $service->sync();

        $this->assertTrue($result);
    }

    public function test_returns_true_when_status_is_ok(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'OK'], 200),
        ]);

        $service = new FiscautConnectorService('12345678000199');
        $result = $service->sync();

        $this->assertTrue($result);
    }

    public function test_returns_false_when_status_is_not_ok(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'ERROR', 'message' => 'Sync failed'], 200),
        ]);

        $service = new FiscautConnectorService('12345678000199');
        $result = $service->sync();

        $this->assertFalse($result);
    }

    public function test_throws_exception_on_http_4xx(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Not found'], 404),
        ]);

        $service = new FiscautConnectorService('12345678000199');

        $this->expectException(FiscautConnectorException::class);
        $service->sync();
    }

    public function test_throws_exception_on_http_5xx(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Server error'], 500),
        ]);

        $service = new FiscautConnectorService('12345678000199');

        $this->expectException(FiscautConnectorException::class);
        $service->sync();
    }

    public function test_throws_exception_when_api_key_is_missing(): void
    {
        Config::set('admin.fiscaconnector_api_key', '');

        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message) => str_contains($message, 'Chave de API'));

        $service = new FiscautConnectorService('12345678000199');

        $this->expectException(FiscautConnectorException::class);
        $service->sync();
    }

    public function test_uses_bearer_token_authentication(): void
    {
        Http::fake([
            '*' => Http::response(['status' => 'OK'], 200),
        ]);

        $apiKey = Config::get('admin.fiscaconnector_api_key');

        $service = new FiscautConnectorService('12345678000199');
        $service->sync();

        Http::assertSent(function ($request) use ($apiKey) {
            $authHeader = $request->header('Authorization')[0] ?? '';

            return str_contains($authHeader, 'Bearer') && str_contains($authHeader, $apiKey);
        });
    }

    public function test_throws_exception_when_response_body_is_empty_on_error_http_status(): void
    {
        Http::fake([
            '*' => Http::response('', 403),
        ]);

        $service = new FiscautConnectorService('12345678000199');

        $this->expectException(FiscautConnectorException::class);
        $service->sync();
    }
}
