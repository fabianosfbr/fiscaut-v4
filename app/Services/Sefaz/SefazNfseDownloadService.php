<?php

namespace App\Services\Sefaz;

use App\Models\Issuer;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;

class SefazNfseDownloadService
{
  
    private Issuer $issuer;

    private array $config;

    private ?Certificate $certificate = null;

    public function __construct(Issuer $issuer)
    {
        $this->issuer = $issuer;

        $this->initializeTools();
    }

    protected function initializeTools(): void
    {
        try {
       
            // Carrega o certificado digital
            $this->loadCertificate();

        } catch (Exception $e) {
            Log::error('Erro ao inicializar ferramentas NFSE', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha na inicialização do serviço: '.$e->getMessage());
        }
    }



    private function loadCertificate(): void
    {
        if (! $this->issuer->certificado_content) {
            throw new Exception('Certificado digital não encontrado para a empresa');
        }

        try {
            // Descriptografa o certificado se necessário
            $certificateContent = Crypt::decrypt($this->issuer->certificado_content);
            $certificatePassword = Crypt::decrypt($this->issuer->senha_certificado);

            $this->certificate = Certificate::readPfx($certificateContent, $certificatePassword);
        } catch (Exception $e) {
            Log::error('Erro ao carregar certificado digital', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao carregar certificado digital: '.$e->getMessage());
        }
    }

    /**
     * Download NFSE documents in batch or specific NSU.
     */
    public function downloadNfseInBatch(?string $ultNsu = null, ?string $nsu = null): array
    {
        $allDocuments = [];
        $currentNsu = $ultNsu ? (int) $ultNsu : $this->getLastSavedNsu();
        $initialNsu = $currentNsu;
        $iterations = 0;
        $shouldStop = false;
        $loopLimit = $nsu ? 1 : 50;

        try {
            do {
                $iterations++;

                if ($iterations > $loopLimit) {
                    break;
                }

                $result = $this->downloadNfseByUltNsu(nsu: $nsu, ultNsu: $currentNsu);

                // Verifica se a SEFAZ solicitou parada (códigos 137 ou 656)
                if ($result['deve_parar']) {
                    Log::warning('SEFAZ solicitou parada nas consultas NFSE', [
                        'issuer_id' => $this->issuer->id,
                        'status' => $result['status'],
                        'motivo' => $result['motivo'],
                    ]);
                    $shouldStop = true;
                    break;
                }

                if (! empty($result['documentos'])) {
                    $allDocuments = array_merge($allDocuments, $result['documentos']);
                }

                // Se for consulta por NSU específico, não continua o loop
                if ($nsu) {
                    break;
                }

                // Atualiza o NSU para a próxima consulta
                if ($result['ultNSU'] && $result['ultNSU'] !== $currentNsu) {
                    $currentNsu = $result['ultNSU'];
                } else {
                    // Se não há novo NSU, para o loop
                    break;
                }

                // Se não encontrou documentos, para o loop
                if (empty($result['documentos'])) {
                    break;
                }

            } while (true);

            Log::info($nsu ? 'Consulta de NSU específico NFSE concluída' : 'Download em lote NFSE concluído', [
                'issuer_id' => $this->issuer->id,
                'nsu_inicial' => $nsu ?: $initialNsu,
                'nsu_final' => $currentNsu,
                'nsu_issuer_atualizado' => $this->issuer->fresh()->ult_nfse_nsu,
                'total_documentos' => count($allDocuments),
                'iterations' => $iterations,
            ]);

            return [
                'documentos' => $allDocuments,
                'total_documentos' => count($allDocuments),
                'iterations' => $iterations,
                'ultimo_nsu' => $currentNsu,
                'nsu_inicial' => $initialNsu,
                'deve_aguardar' => $shouldStop,
            ];
        } catch (Exception $e) {
            Log::error('Erro no download de NFSE', [
                'issuer_id' => $this->issuer->id,
                'nsu_especifico' => $nsu,
                'nsu_inicial' => $initialNsu ?? 'N/A',
                'nsu_atual' => $currentNsu ?? 'N/A',
                'iterations' => $iterations,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha no download: '.$e->getMessage());
        }
    }

    /**
     * Realiza o download de NFSE usando o último NSU (lote) ou um NSU específico.
     *
     * @param  string|null  $nsu  NSU específico para consulta (se informado, faz consulta específica)
     * @param  string|null  $ultNsu  NSU inicial para consulta em lote (opcional, usa o NSU do issuer se não informado)
     *
     * @throws Exception
     */
    private function downloadNfseByUltNsu(?string $nsu = null, ?string $ultNsu = null): array
    {
        try {
            if ($nsu) {
                // Consulta específica por NSU
                $currentNsu = $nsu;
                $response = $this->getDistDfe(ultNsu: $currentNsu);
            } else {
                // Para consultas em lote, sempre usa o NSU da empresa se não informado
                $currentNsu = $ultNsu ?: $this->getLastSavedNsu();
                $response = $this->getDistDfe(ultNsu: $currentNsu);
            }


            Log::channel('sefaz_log')->info(
                $nsu ?
                    "Log de consulta NFSE - SEFAZ - registro específico - " . explode(':', $this->issuer->razao_social)[0] . " : \n" . substr($response, 0, 2000) :
                    "Log de consulta NFSE - SEFAZ - registro em lote - " . explode(':', $this->issuer->razao_social)[0] . " : \n" . substr($response, 0, 2000)
            );

            // Processa a resposta
            $result = $this->processDistDFeResponse($response);

            // SEMPRE atualiza o NSU da empresa em consultas em lote
            if ($result['ultNSU']) {
                $this->saveLastNsu((int) $result['ultNSU']);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Erro no download de NFSE por NSU (consulta em lote)', [
                'issuer_id' => $this->issuer->id,
                'ult_nsu' => $currentNsu ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Falha no download de NFSE: '.$e->getMessage());
        }
    }

    private function getLastSavedNsu(): int
    {
        try {
            // Busca nas configurações da empresa
            if ($this->issuer->ult_nfse_nsu) {
                return $this->issuer->ult_nfse_nsu;
            }

            // Se não encontrou, retorna NSU inicial
            return 0;
        } catch (Exception $e) {
            Log::warning('Erro ao buscar último NSU NFSE, usando NSU inicial', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    private function saveLastNsu(int $nsu): void
    {
        try {
            // Atualiza as configurações da empresa com o último NSU
            $this->issuer->updateQuietly([
                'ult_nfse_nsu' => (int) $nsu,
                'ultima_consulta_nfse' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao salvar NSU NFSE', [
                'issuer_id' => $this->issuer->id,
                'nsu' => $nsu,
                'error' => $e->getMessage(),
            ]);
            // Não propaga o erro para não interromper o fluxo principal
        }
    }

    /**
     * Processa a resposta da consulta de distribuição DFe
     *
     * @throws Exception
     */
    private function processDistDFeResponse(string $response): array
    {
        try {
            // Decodifica a resposta JSON do ADN
            $data = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Resposta inválida da SEFAZ - formato JSON inválido: '.json_last_error_msg());
            }

            if (! isset($data->StatusProcessamento)) {
                throw new Exception('Resposta inválida da SEFAZ - campo StatusProcessamento não encontrado');
            }

            $result = [
                'status' => $data->StatusProcessamento,
                'motivo' => $data->Descricao ?? 'N/A',
                'ambiente' => config('admin.environment.HAMBIENTE_SEFAZ'),
                'versao_aplicacao' => $data->VersaoAplicacao ?? 'N/A',
                'data_resposta' => now()->toISOString(),
                'ultNSU' => null,
                'maxNSU' => null,
                'documentos' => [],
                'xml_response' => $response,
                'deve_parar' => false,
            ];

            // Verifica se há documentos localizados
            if ($data->StatusProcessamento === 'DOCUMENTOS_LOCALIZADOS' && isset($data->LoteDFe)) {
                foreach ($data->LoteDFe as $DFe) {
                    $nsuAtual = isset($DFe->NSU) ? (int) $DFe->NSU : null;
                    if ($nsuAtual !== null) {
                        if (! $result['ultNSU'] || $nsuAtual > $result['ultNSU']) {
                            $result['ultNSU'] = $nsuAtual;
                        }
                    }

                    $documento = $this->processarDocumento($DFe);
                    if ($documento) {
                        $result['documentos'][] = $documento;
                    }
                }

                // Define o NSU máximo baseado no maior NSU encontrado
                $result['maxNSU'] = $result['ultNSU'];
            } elseif ($data->StatusProcessamento === 'NENHUM_DOCUMENTO_LOCALIZADO') {
                //Não fazer nada
            } else {
                // Outros status possíveis indicam problemas
                Log::warning('Status inesperado na resposta SEFAZ NFSE', [
                    'issuer_id' => $this->issuer->id,
                    'status' => $data->StatusProcessamento,
                    'descricao' => $data->Descricao ?? 'N/A',
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Erro ao processar resposta SEFAZ NFSE', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
                'response' => substr($response, 0, 200).'...',
            ]);
            throw new Exception('Falha ao processar resposta SEFAZ: '.$e->getMessage());
        }
    }

    private function decodeArquivoXmlToXmlString(?string $arquivoXml, ?string $chaveAcesso, ?int $nsu): ?string
    {
        if (! is_string($arquivoXml) || trim($arquivoXml) === '') {
            Log::error('ArquivoXml ausente no documento NFSE', [
                'chave_acesso' => $chaveAcesso ?? 'N/A',
                'nsu' => $nsu ?? 'N/A',
            ]);

            return null;
        }

        $decoded = base64_decode($arquivoXml, true);
        if ($decoded === false) {
            $normalized = strtr($arquivoXml, '-_', '+/');
            $padding = strlen($normalized) % 4;
            if ($padding > 0) {
                $normalized .= str_repeat('=', 4 - $padding);
            }
            $decoded = base64_decode($normalized, true);
        }
        if ($decoded === false) {
            Log::error('Falha ao decodificar base64 do documento NFSE', [
                'chave_acesso' => $chaveAcesso ?? 'N/A',
                'nsu' => $nsu ?? 'N/A',
            ]);

            return null;
        }

        $xml = @gzdecode($decoded);
        if ($xml === false) {
            $xml = @gzuncompress($decoded);
        }
        if ($xml === false) {
            $xml = @gzinflate($decoded);
        }
        if ($xml === false && strlen($decoded) > 2) {
            $xml = @gzinflate(substr($decoded, 2));
        }
        if ($xml === false) {
            $candidate = ltrim($decoded);
            if ($candidate !== '' && str_starts_with($candidate, '<')) {
                $xml = $decoded;
            }
        }

        if (! is_string($xml) || trim($xml) === '') {
            Log::error('Falha ao descompactar/obter XML do documento NFSE', [
                'chave_acesso' => $chaveAcesso ?? 'N/A',
                'nsu' => $nsu ?? 'N/A',
            ]);

            return null;
        }

        return ltrim($xml, "\xEF\xBB\xBF");
    }

    /**
     * Processa um único documento DFe
     */
    private function processarDocumento($DFe): ?array
    {
        try {
            // Decodifica o XML do documento
            $chaveAcesso = $DFe->ChaveAcesso ?? null;
            $nsu = isset($DFe->NSU) ? (int) $DFe->NSU : null;
            $xml = $this->decodeArquivoXmlToXmlString($DFe->ArquivoXml ?? null, $chaveAcesso, $nsu);

            if ($xml === null) {
                return null;
            }

            // Determina o tipo de documento
            $tipoDocumento = $DFe->TipoDocumento ?? 'DESCONHECIDO';

            return [
                'nsu' => $nsu,
                'chave_acesso' => $chaveAcesso,
                'tipo_documento' => $tipoDocumento,
                'xml' => $xml,
                'data_hora_geracao' => $DFe->DataHoraGeracao ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Erro ao processar documento NFSE', [
                'nsu' => $DFe->NSU ?? 'N/A',
                'chave_acesso' => $DFe->ChaveAcesso ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Consulta o DF-e no ADN SEFAZ
     */
    private function getDistDfe($ultNsu = 0)
    {
        $certificadoContent = Crypt::decrypt($this->issuer->certificado_content);
        $certificado = Certificate::readPfx($certificadoContent, Crypt::decrypt($this->issuer->senha_certificado));

        $cnpjConsulta = $this->issuer->cnpj;

        $url = "https://adn.nfse.gov.br/contribuintes/dfe/$ultNsu?cnpjConsulta=$cnpjConsulta";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT_BLOB, $certificado->publicKey);
        curl_setopt($ch, CURLOPT_SSLKEY_BLOB, $certificado->privateKey);

        // Configuração para não verificar o certificado do servidor (em ambiente de desenvolvimento)
        // Em produção, é recomendável manter a verificação
        if (config('app.env') === 'local' || config('app.env') === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $serverResponse = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode === 404) {
            if (! is_string($serverResponse) || trim($serverResponse) === '') {
                return json_encode([
                    'StatusProcessamento' => 'NENHUM_DOCUMENTO_LOCALIZADO',
                    'Descricao' => 'Nenhum documento localizado (HTTP 404)',
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        if ($httpCode != 200 && $httpCode != 404) {
            throw new Exception("Erro na consulta DFe ADN: HTTP Code $httpCode - Error: $error - Response: $serverResponse");
        }

        sleep(2);

        return (string) $serverResponse;
    }

    /**
     * Obtém a DANFSE para uma chave de acesso específica
     */
    public function getDanfse(string $chaveAcesso)
    {
        $certificadoContent = Crypt::decrypt($this->issuer->certificado_content);
        $certificado = Certificate::readPfx($certificadoContent, Crypt::decrypt($this->issuer->senha_certificado));

        $url = "https://adn.nfse.gov.br/danfse/$chaveAcesso";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT_BLOB, $certificado->publicKey);
        curl_setopt($ch, CURLOPT_SSLKEY_BLOB, $certificado->privateKey);

        // Configuração para não verificar o certificado do servidor (em ambiente de desenvolvimento)
        // Em produção, é recomendável manter a verificação
        if (config('app.env') === 'local' || config('app.env') === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $serverResponse = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode != 200) {
            throw new Exception("Erro ao gerar Danfse: HTTP Code $httpCode - Error: $error");
        }

        return $serverResponse;
    }
}
