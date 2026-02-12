<?php

namespace App\Services\Sefaz;

use App\Models\Issuer;
use App\Services\Xml\XmlIdentifierService;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use NFePHP\Common\Certificate;
use NFePHP\CTe\Tools;

class SefazCteDownloadService
{
    private Tools $tools;

    private Issuer $issuer;

    private array $config;

    private Certificate $certificate;

    public function __construct(Issuer $issuer)
    {
        $this->issuer = $issuer;

        $this->initializeTools();
    }

    protected function initializeTools(): void
    {
        try {

            if ($this->shouldMockDistDFe()) {
                return;
            }

            // Busca as configurações da empresa
            $this->loadIssuerConfig();

            // Carrega o certificado digital
            $this->loadCertificate();

            // Inicializa as ferramentas NFePHP
            $this->tools = new Tools(json_encode($this->config), $this->certificate);
            $contingencia = $this->tools->contingency->deactivate();
            $this->tools->contingency->load($contingencia);
        } catch (Exception $e) {
            Log::error('Erro ao inicializar ferramentas CTePHP', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha na inicialização do serviço: '.$e->getMessage());
        }
    }

    private function loadIssuerConfig(): void
    {
        $this->config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => config('admin.environment.HAMBIENTE_SEFAZ'),
            'razaosocial' => explode(':', $this->issuer->razao_social)[0],
            'cnpj' => $this->issuer->cnpj,
            'siglaUF' => $this->issuer->municipio()->first()?->sigla ?? 'SP',
            'schemes' => 'PL_CTe_400',
            'versao' => '4.00',
        ];
    }

    private function loadCertificate(): void
    {
        if (! $this->issuer->certificado_content) {
            throw new Exception('Certificado digital não encontrado para a empresa');
        }

        try {
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

    private function shouldMockDistDFe(): bool
    {
        return (bool) config('sefaz.distdfe.mock.enabled', false);
    }

    private function getMockDistDFeResponse(): string
    {
        $path = (string) config('sefaz.distdfe.mock.cte_path', '');
        if ($path === '' || ! is_file($path)) {
            throw new Exception('Mock SEFAZ distDFe habilitado, mas o arquivo não foi encontrado.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new Exception('Falha ao ler o arquivo de mock SEFAZ distDFe.');
        }

        return $contents;
    }

    private function getDistDFeSleepSeconds(): int
    {
        if ($this->shouldMockDistDFe()) {
            return 0;
        }

        return max(0, (int) config('sefaz.distdfe.sleep_seconds', 2));
    }

    private function getTools(): Tools
    {
        if (! $this->tools) {
            throw new Exception('Ferramentas NFePHP não inicializadas para este issuer.');
        }

        return $this->tools;
    }

    /**
     * Download CTe documents in batch or specific NSU.
     */
    public function downloadCteInBatch(?string $ultNsu = null, ?string $nsu = null): array
    {
        $allDocuments = [];
        $currentNsu = $ultNsu ? (int) $ultNsu : $this->getLastSavedNsu();
        $initialNsu = $currentNsu;
        $iterations = 0;
        $shouldStop = false;
        $loopLimit = $nsu ? 1 : 50; // Se for NSU específico, faz apenas uma iteração
        $sleepSeconds = $this->getDistDFeSleepSeconds();

        try {

            do {
                $iterations++;

                if ($iterations > $loopLimit) {
                    break;
                }

                $result = $this->downloadCteByUltNsu(nsu: $nsu, ultNsu: $currentNsu);

                // Verifica se a SEFAZ solicitou parada (códigos 137 ou 656)
                if ($result['deve_parar']) {
                    Log::warning('SEFAZ solicitou parada nas consultas', [
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

                // Verifica se atingiu o NSU máximo
                if ($result['ultNSU'] && $result['maxNSU'] && (int) $result['ultNSU'] == (int) $result['maxNSU']) {
                    $currentNsu = $result['ultNSU'];
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

                // Pausa entre consultas conforme exemplo da nfephp (2 segundos)
                if ($sleepSeconds > 0) {
                    sleep($sleepSeconds);
                }
            } while (true);

            Log::info($nsu ? 'Consulta de NSU específico CTe concluída' : 'Download em lote de CTe concluído', [
                'issuer_id' => $this->issuer->id,
                'nsu_inicial' => $nsu ?: $initialNsu,
                'nsu_final' => $currentNsu,
                'total_documentos' => count($allDocuments),
                'iterations' => $iterations,
            ]);

            return [
                'documentos' => $allDocuments,
                'total_documentos' => count($allDocuments),
                'iterations' => $iterations,
                'ultimo_nsu' => $currentNsu,
            ];
        } catch (Exception $e) {
            Log::error('Erro no download de CTe', [
                'issuer_id' => $this->issuer->id,
                'nsu_especifico' => $nsu,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha no download: '.$e->getMessage());
        }
    }

    /**
     * Realiza o download de CTe usando o último NSU (lote) ou um NSU específico.
     *
     * @param  string|null  $nsu  NSU específico para consulta (se informado, faz consulta específica)
     * @param  string|null  $ultNsu  NSU inicial para consulta em lote (opcional, usa o NSU do issuer se não informado)
     *
     * @throws Exception
     */
    private function downloadCteByUltNsu(?string $nsu = null, ?string $ultNsu = null): array
    {
        try {
            if ($nsu) {
                // Consulta específica por NSU
                $currentNsu = $nsu;
                $response = $this->shouldMockDistDFe()
                    ? $this->getMockDistDFeResponse()
                    : $this->getTools()->sefazDistDFe(0, $currentNsu);
            } else {
                // Consulta por último NSU (lote)
                $currentNsu = $ultNsu ?: $this->getLastSavedNsu();
                $response = $this->shouldMockDistDFe()
                    ? $this->getMockDistDFeResponse()
                    : $this->getTools()->sefazDistDFe($currentNsu);
            }

            Log::channel('sefaz_log')->info(
                $nsu ?
                    "Log de consulta CTE - SEFAZ - registro específico - " . explode(':', $this->issuer->razao_social)[0] . " : \n" . $response :
                    "Log de consulta CTE - SEFAZ - registro em lote - " . explode(':', $this->issuer->razao_social)[0] . " : \n" . $response
            );

            $result = $this->processDistDFeResponse($response);

            if ($result['ultNSU']) {
                $this->saveLastNsu((int) $result['ultNSU']);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Erro no download de CTe por NSU (consulta em lote)', [
                'issuer_id' => $this->issuer->id,
                'ult_nsu' => $currentNsu ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Falha no download de CTe: '.$e->getMessage());
        }
    }

    private function getLastSavedNsu(): int
    {
        return $this->issuer->ult_nsu_cte ?? 0;
    }

    private function saveLastNsu(int $nsu): void
    {
        $this->issuer->updateQuietly([
            'ult_nsu_cte' => (int) $nsu,
            'ultima_consulta_cte' => now(),
        ]);
    }

    private function processDistDFeResponse(string $xmlResponse): array
    {
        $dom = new \DOMDocument;
        $dom->loadXML($xmlResponse);
        $node = $dom->getElementsByTagName('retDistDFeInt')->item(0);

        if (! $node) {
            throw new Exception('Resposta inválida da SEFAZ - elemento retDistDFeInt não encontrado');
        }

        $tpAmb = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        $verAplic = $node->getElementsByTagName('verAplic')->item(0)->nodeValue;
        $cStat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
        $xMotivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        $dhResp = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
        $ultNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue ?? null;
        $maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue ?? null;
        $lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);

        $result = [
            'status' => $cStat,
            'motivo' => $xMotivo,
            'ambiente' => $tpAmb,
            'versao_aplicacao' => $verAplic,
            'data_resposta' => $dhResp,
            'ultNSU' => $ultNSU,
            'maxNSU' => $maxNSU,
            'documentos' => [],
            'xml_response' => $xmlResponse,
            'deve_parar' => false,
        ];

        // Verifica códigos de status conforme documentação
        if (in_array($cStat, ['137', '656'])) {
            // 137 - Nenhum documento localizado (aguardar 1 hora)
            // 656 - Consumo Indevido (bloqueado por 1 hora)
            $result['deve_parar'] = true;
            Log::warning('SEFAZ solicita parada nas consultas', [
                'issuer_id' => $this->issuer->id,
                'status' => $cStat,
                'motivo' => $xMotivo,
            ]);

            return $result;
        }

        // Verifica se houve sucesso na consulta
        if ($cStat == '138') { // Documento(s) localizado(s)
            if (! empty($lote)) {
                $result['documentos'] = $this->extractDocumentsFromLote($lote, $maxNSU);
            }
        } elseif ($cStat == '137') { // Nenhum documento localizado
            Log::info('Nenhum documento localizado para o NSU informado', [
                'issuer_id' => $this->issuer->id,
                'status' => $cStat,
            ]);
        } else {
            // Outros códigos de status (erros)
            throw new Exception("Erro na consulta SEFAZ: {$cStat} - {$xMotivo}");
        }

        return $result;
    }

    private function extractDocumentsFromLote(\DOMElement $lote, ?string $maxNSU = null): array
    {
        try {
            $documentos = [];
            $docs = $lote->getElementsByTagName('docZip');

            foreach ($docs as $doc) {
                try {
                    $nsu = $doc->getAttribute('NSU');
                    $schema = $doc->getAttribute('schema');
                    $contentZipped = $doc->nodeValue;

                    // Descompacta o documento conforme exemplo da nfephp
                    $content = gzdecode(base64_decode($contentZipped));

                    if ($content !== false) {
                        $tipoDocumento = XmlIdentifierService::identificarTipoXml($content);
                        $documentos[] = [
                            'nsu' => $nsu,
                            'max_nsu' => $maxNSU,
                            'tipo_documento' => $tipoDocumento,
                            'schema' => $schema,
                            'xml_content' => $content,
                        ];
                    } else {
                        Log::warning('Falha ao descompactar documento', [
                            'issuer_id' => $this->issuer->id,
                            'nsu' => $nsu,
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning('Erro ao extrair documento do lote', [
                        'issuer_id' => $this->issuer->id,
                        'nsu' => $nsu ?? 'N/A',
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Erro ao processar lote de documentos', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $documentos;
    }

    public function sefazManifesta(string $chCTe, string $tpEvento, string $xJust, int $nSeqEvento, string $uf): string
    {
        return $this->tools->sefazManifesta($chCTe, $tpEvento, $xJust, $nSeqEvento, $uf);
    }
}
