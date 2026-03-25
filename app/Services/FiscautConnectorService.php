<?php

namespace App\Services;

use App\Exceptions\FiscautConnectorException;
use App\Models\Issuer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FiscautConnectorService
{
    public function __construct(
        protected Issuer $issuer,
    ) {}

    public function sync(): array
    {
        $tenant = $this->issuer->tenant()->first();
        $url = $tenant?->fiscaut_connector_url;
        $apiKey = $tenant?->fiscaut_connector_token;

        if (empty($apiKey)) {
            Log::error('FiscautConnectorException: Chave de API do FiscautConnector não configurada.');

            throw new FiscautConnectorException('Chave de API do FiscautConnector não configurada.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'cgce_emp' => $this->issuer->cnpj,
                    'sync' => true,
                ]);

            if (! $response->successful()) {
                Log::error('FiscautConnectorException: Erro HTTP '.$response->status().' — '.$response->body());

                throw new FiscautConnectorException(
                    'Erro na requisição ao FiscautConnector: HTTP '.$response->status()
                );
            }

            $data = $response->json();

            return $data;
        } catch (FiscautConnectorException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('FiscautConnectorException: '.$e->getMessage());

            throw new FiscautConnectorException(
                'Erro de conexão com o FiscautConnector: '.$e->getMessage()
            );
        }
    }
}
