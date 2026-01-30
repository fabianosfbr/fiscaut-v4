<?php

namespace App\Services\Sefaz;

use App\Models\Issuer;
use App\Models\LogSefazManifestoEvent;
use App\Models\LogSefazResumoNfe;
use App\Services\Xml\XmlIdentifierService;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Tools;

class SefazNfeDownloadService
{
    private ?Tools $tools = null;

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
            if ($this->shouldMockDistDFe()) {
                return;
            }


            // Busca as configurações da empresa
            $this->loadIssuerConfig();

            // Carrega o certificado digital
            $this->loadCertificate();


            // Inicializa as ferramentas NFePHP
            $this->tools = new Tools(json_encode($this->config), $this->certificate);
            $this->tools->model('55'); // Modelo 55 para NFe

        } catch (Exception $e) {
            Log::error('Erro ao inicializar ferramentas NFePHP', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha na inicialização do serviço: ' . $e->getMessage());
        }
    }

    private function loadIssuerConfig(): void
    {
        $this->config = [
            'atualizacao' => date('Y-m-d h:i:s'),
            'tpAmb' => config('admin.environment.HAMBIENTE_SEFAZ'),
            'razaosocial' => explode(':', $this->issuer->razao_social)[0],
            'siglaUF' => $this->issuer->municipio()->first()?->sigla ?? 'SP',
            'cnpj' => $this->issuer->cnpj,
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tokenIBPT' => '',
            'CSC' => '',
            'CSCid' => '',
            'aProxyConf' => [
                'proxyIp' => '',
                'proxyPort' => '',
                'proxyUser' => '',
                'proxyPass' => '',
            ],
        ];
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
            throw new Exception('Falha ao carregar certificado digital: ' . $e->getMessage());
        }
    }

    /**
     * Download NFe documents in batch.
     */
    public function downloadNfeInBatch(?string $ultNsu = null): array
    {
        $allDocuments = [];
        $currentNsu = $ultNsu ? (int) $ultNsu : $this->getLastSavedNsu();
        $initialNsu = $currentNsu;
        $iterations = 0;
        $shouldStop = false;
        $loopLimit = 50;
        $sleepSeconds = $this->getDistDFeSleepSeconds();

        try {
            Log::info('Iniciando download em lote de NFe', [
                'issuer_id' => $this->issuer->id,
                'nsu_inicial' => $currentNsu,
                'nsu_issuer_atual' => $this->issuer->ult_nsu_nfe,
            ]);

            do {
                $iterations++;

                if ($iterations > $loopLimit) {
                    break;
                }

                $result = $this->downloadNfeByUltNsu($currentNsu);

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

            Log::info('Download em lote concluído', [
                'issuer_id' => $this->issuer->id,
                'nsu_inicial' => $initialNsu,
                'nsu_final' => $currentNsu,
                'nsu_issuer_atualizado' => $this->issuer->fresh()->ult_nsu_nfe,
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
            Log::error('Erro no download em lote de NFe', [
                'issuer_id' => $this->issuer->id,
                'nsu_inicial' => $initialNsu ?? 'N/A',
                'nsu_atual' => $currentNsu ?? 'N/A',
                'iterations' => $iterations,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha no download em lote: ' . $e->getMessage());
        }
    }

    /**
     * Realiza o download de NFe usando o último NSU (para consultas em lote)
     * Este método sempre atualiza o NSU do issuer com o último NSU retornado
     *
     * @param  string|null  $ultNsu  NSU inicial (opcional, usa o NSU do issuer se não informado)
     *
     * @throws Exception
     */
    private function downloadNfeByUltNsu(?string $ultNsu = null): array
    {
        try {
            // Para consultas em lote, sempre usa o NSU da empresa se não informado
            $currentNsu = $ultNsu ?: $this->getLastSavedNsu();

            $response = $this->shouldMockDistDFe()
                ? $this->getMockDistDFeResponse()
                : $this->getTools()->sefazDistDFe($currentNsu);

            Log::channel('sefaz_log')->info('Log de consulta NFe - SEFAZ - registro - ' . explode(':', $this->issuer->razao_social)[0] . ' : ' . $response);
            // Processa a resposta
            $result = $this->processDistDFeResponse($response);

            // SEMPRE atualiza o NSU da empresa em consultas em lote
            if ($result['ultNSU']) {
                $this->saveLastNsu((int) $result['ultNSU']);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Erro no download de NFe por NSU (consulta em lote)', [
                'issuer_id' => $this->issuer->id,
                'ult_nsu' => $currentNsu ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Falha no download de NFe: ' . $e->getMessage());
        }
    }

    private function getLastSavedNsu(): int
    {

        try {
            // Busca nas configurações da empresa
            if ($this->issuer->ult_nsu_nfe) {
                return $this->issuer->ult_nsu_nfe;
            }

            // Se não encontrou, retorna NSU inicial
            return 0;
        } catch (Exception $e) {
            Log::warning('Erro ao buscar último NSU, usando NSU inicial', [
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
                'ult_nsu_nfe' => (int) $nsu,
                'ultima_consulta_nfe' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao salvar NSU', [
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
    private function processDistDFeResponse(string $xmlResponse): array
    {
        try {
            // Usa DOMDocument conforme exemplo da biblioteca nfephp
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
        } catch (Exception $e) {
            Log::error('Erro ao processar resposta da distribuição DFe', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao processar resposta: ' . $e->getMessage());
        }
    }

    /**
     * Extrai documentos do lote de distribuição usando DOMDocument
     */
    private function extractDocumentsFromLote(\DOMElement $lote, ?string $maxNSU = null): array
    {
        $documentos = [];

        try {
            // Busca todas as tags docZip no lote
            $docs = $lote->getElementsByTagName('docZip');

            foreach ($docs as $doc) {
                try {
                    $nsu = $doc->getAttribute('NSU');
                    $schema = $doc->getAttribute('schema');
                    $contentZipped = $doc->nodeValue;

                    if ($contentZipped) {
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

    private function getTools(): Tools
    {
        if (! $this->tools) {
            throw new Exception('Ferramentas NFePHP não inicializadas para este issuer.');
        }

        return $this->tools;
    }

    private function shouldMockDistDFe(): bool
    {
        return (bool) config('sefaz.distdfe.mock.enabled', false);
    }

    private function getMockDistDFeResponse(): string
    {
        $path = (string) config('sefaz.distdfe.mock.path', '');
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

    /**
     * Verifica o status do serviço SEFAZ
     *
     * @throws Exception
     */
    public function checkSefazStatus(): array
    {
        try {
            $response = $this->getTools()->sefazStatus();

            $standardize = new Standardize;
            $std = $standardize->toStd($response);

            $isOnline = in_array($std->cStat, ['107', '108']); // Serviço em operação

            return [
                'online' => $isOnline,
                'status' => $std->cStat,
                'motivo' => $std->xMotivo,
                'uf' => $std->cUF ?? null,
                'ambiente' => $std->tpAmb,
                'versao_aplicacao' => $std->verAplic ?? null,
                'tempo_medio' => $std->tMed ?? null,
                'data_resposta' => $std->dhRecbto ?? null,
                'xml_response' => $response,
            ];
        } catch (Exception $e) {
            Log::error('Erro ao verificar status SEFAZ', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao verificar status SEFAZ: ' . $e->getMessage());
        }
    }

    /**
     * Realiza a manifestação de uma NF-e.
     */
    public function sefazManifesta(string $chNFe, string $tpEvento, string $xJust = '', int $nSeqEvento = 1): bool
    {
        $response = $this->getTools()->sefazManifesta($chNFe, $tpEvento, $xJust, $nSeqEvento);

        Log::info('Log de manifestação NFe - SEFAZ', [
            'issuer' => $this->issuer->razao_social,
            'chave' => $chNFe,
            'response' => $response,
        ]);

        $standardize = new Standardize($response);
        $std = $standardize->toStd();

        $logSefaz = LogSefazManifestoEvent::create([
            'issuer_id' => $this->issuer->id,
            'chave' => $chNFe,
            'type' => 'nfe',
            'tpEvento' => $std->retEvento->infEvento->tpEvento,
            'cStat' => $std->cStat,
            'xMotivo' => $std->xMotivo,
            'justificativa' => $xJust,
            'infEvento_cStat' => $std->retEvento->infEvento->cStat,
            'infEvento_xMotivo' => $std->retEvento->infEvento->xMotivo,
            'xml' => $response,
        ]);

        if ($logSefaz->cStat == '128' && $std->retEvento->infEvento->tpEvento == '210200') {
            return true;
        }

        return false;
    }

    /**
     * Manifesta ciência da operação para todos os resumos pendentes.
     */
    public function manifestaCienciaDaOperacao(): void
    {
        $resumos = LogSefazResumoNfe::where('issuer_id', $this->issuer->id)
            ->where('is_ciente_operacao', false)
            ->get();

        foreach ($resumos as $resumo) {
            try {
                $response = $this->sefazManifesta($resumo->chave, '210210'); // Ciência da Operação

                Log::info('Log de manifestação NFe - SEFAZ', [
                    'issuer' => $this->issuer->razao_social,
                    'chave' => $resumo->chave,
                    'response' => $response,
                ]);

                $standardize = new Standardize($response);
                $std = $standardize->toStd();

                LogSefazManifestoEvent::create([
                    'issuer_id' => $this->issuer->id,
                    'chave' => $resumo->chave,
                    'type' => 'nfe',
                    'tpEvento' => $std->retEvento->infEvento->tpEvento,
                    'cStat' => $std->cStat,
                    'xMotivo' => $std->xMotivo,
                    'infEvento_cStat' => $std->retEvento->infEvento->cStat,
                    'infEvento_xMotivo' => $std->retEvento->infEvento->xMotivo,
                    'xml' => $response,
                ]);

                $resumo->update([
                    'data_ciencia_manifesto' => now(),
                    'is_ciente_operacao' => true,
                ]);
            } catch (Exception $e) {
                Log::error('Erro ao manifestar ciência da operação', [
                    'issuer_id' => $this->issuer->id,
                    'chave' => $resumo->chave,
                    'error' => $e->getMessage(),
                ]);
            }

            sleep(2);
        }
    }
}
