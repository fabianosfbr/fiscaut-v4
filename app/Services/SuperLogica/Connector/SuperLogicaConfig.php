<?php

namespace App\Services\SuperLogica\Connector;

use App\Exceptions\SuperlogicaConnectionException;
use App\Models\Tenant;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

trait SuperLogicaConfig
{
    public function __construct(
        protected Tenant $tenant,
        protected ?PendingRequest $http = null
    ) {

        $baseUrl = trim((string) ($tenant?->superlogica_base_url ?? ''));
        $appToken = trim((string) ($tenant?->superlogica_app_token ?? ''));
        $accessToken = trim((string) ($tenant?->superlogica_access_token ?? ''));

        if ($baseUrl === '') {
            throw new SuperlogicaConnectionException('URL base da Superlógica não configurada.');
        }

        if ($appToken === '') {
            throw new SuperlogicaConnectionException('app_token da Superlógica não configurado.');
        }

        if ($accessToken === '') {
            throw new SuperlogicaConnectionException('access_token da Superlógica não configurado.');
        }

        $this->http = Http::withToken($accessToken)
            ->withHeaders([
                'app_token' => $appToken,
                'access_token' => $accessToken,
            ])
            ->timeout(0)  // Sem timeout (0 = infinito)
            ->baseUrl($baseUrl);
    }

    public function get(string $url, ?array $params = null)
    {

        try {
            return $this->http
                ->get($url, $params)
                ->throw()
                ->json();
        } catch (RequestException  $e) {
            $response = $e->response;

            return $response->json();
        }
    }

        public function getFile(string $url, ?array $params = null)
    {

        try {
            return $this->http
                ->get($url, $params)
                ->throw()
                ->body();
        } catch (RequestException  $e) {
            $response = $e->response;

            return $response->json();
        }
    }

    public function post(string $url, ?array $params = null)
    {
        try {
            return $this->http
                ->post($url, $params)
                ->throw()
                ->json();
        } catch (RequestException  $e) {
            $response = $e->response;

            return $response->json();
        }
    }

    public function postForm(string $url, ?array $params = null)
    {
        try {
            return $this->http
                ->asForm()
                ->post($url, $params)
                ->throw()
                ->json();
        } catch (RequestException  $e) {
            $response = $e->response;

            return $response->json();
        }
    }

    public function delete(string $url)
    {
        try {
            return $this->http
                ->delete($url)
                ->throw()
                ->json();
        } catch (RequestException  $e) {
            $response = $e->response;

            return $response->json();
        }
    }

    public function put(string $url, array $params)
    {
        try {
            return $this->http
                ->put($url, $params)
                ->throw()
                ->json();
        } catch (RequestException  $e) {
            $response = $e->response;

            return $response->json();
        }
    }

    public function patch(string $url)
    {
        try {
            return $this->http
                ->patch($url)
                ->throw()
                ->json();
        } catch (RequestException  $e) {
            $response = $e->response;

            return $response->json();
        }
    }
}
