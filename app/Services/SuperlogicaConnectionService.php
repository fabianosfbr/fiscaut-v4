<?php

namespace App\Services;

use App\Exceptions\SuperlogicaConnectionException;
use App\Models\Tenant;
use App\Services\SuperLogica\Condominio\SuperLogicaArquivoConnector;
use App\Services\SuperLogica\Condominio\SuperLogicaCondominioConnector;
use App\Services\SuperLogica\Condominio\SuperLogicaCondominioPlanoDeContaConnector;
use App\Services\SuperLogica\Condominio\SuperLogicaDespesaConnector;
use App\Services\SuperLogica\Condominio\SuperLogicaDocumentoConnector;
use App\Services\SuperLogica\Condominio\SuperLogicaReceitaConnector;
use App\Services\SuperLogica\Condominio\SuperLogicaUnidadeConnector;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuperlogicaConnectionService
{
    public function __construct(
        protected Tenant $tenant
    ) {}

    public function condominio()
    {
        return new SuperLogicaCondominioConnector($this->tenant);
    }

    public function unidade()
    {
        return new SuperLogicaUnidadeConnector($this->tenant);
    }

    public function despesa()
    {
        return new SuperLogicaDespesaConnector($this->tenant);
    }

    public function documento()
    {
        return new SuperLogicaDocumentoConnector($this->tenant);
    }

    public function arquivo()
    {
        return new SuperLogicaArquivoConnector($this->tenant);
    }

    public function receita()
    {
        return new SuperLogicaReceitaConnector($this->tenant);
    }

    public function planoDeContas()
    {
        return new SuperLogicaCondominioPlanoDeContaConnector($this->tenant);
    }

    /**
     * @return array<string, mixed>|bool
     *
     * @throws SuperlogicaConnectionException
     */
    public function validateConnection(Tenant $tenant): array|bool
    {
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

        $endpoint = rtrim($baseUrl, '/') . '/health/check';

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'app_token' => $appToken,
                    'access_token' => $accessToken,
                ])
                ->get($endpoint);

            if (!$response->successful()) {
                Log::error('Falha na validação Superlógica', [
                    'tenant_id' => $tenant?->id,
                    'status' => $response->status(),
                ]);

                throw new SuperlogicaConnectionException('Falha de conexão com a Superlógica (HTTP ' . $response->status() . ').');
            }

            $payload = $response->json();

            if (!is_array($payload) || $payload === []) {
                throw new SuperlogicaConnectionException('Resposta inválida da Superlógica no health check.');
            }

            return $payload;
        } catch (ConnectionException $exception) {
            Log::error('Timeout/erro de rede na validação Superlógica', [
                'tenant_id' => $tenant?->id,
            ]);

            throw new SuperlogicaConnectionException('Não foi possível conectar na Superlógica (timeout/rede).', 0, $exception);
        } catch (SuperlogicaConnectionException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Erro inesperado na validação Superlógica', [
                'tenant_id' => $tenant?->id,
                'error' => $exception->getMessage(),
            ]);

            throw new SuperlogicaConnectionException('Erro ao validar conexão com a Superlógica.', 0, $exception);
        }
    }
}
