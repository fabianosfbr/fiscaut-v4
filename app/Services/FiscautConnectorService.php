<?php

namespace App\Services;

use App\Exceptions\FiscautConnectorException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FiscautConnectorService
{
    public function __construct(
        protected string $cgcEmp,
    ) {}

    public function sync(): bool
    {
        $url = config('admin.fiscaconnector_url');
        $apiKey = config('admin.fiscaconnector_api_key');

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
                    'cgc_emp' => $this->cgcEmp,
                    'sync' => true,
                ]);

            if (! $response->successful()) {
                Log::error('FiscautConnectorException: Erro HTTP '.$response->status().' — '.$response->body());

                throw new FiscautConnectorException(
                    'Erro na requisição ao FiscautConnector: HTTP '.$response->status()
                );
            }

            $data = $response->json();
            $status = $data['status'] ?? null;

            return $status === 'OK';
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
