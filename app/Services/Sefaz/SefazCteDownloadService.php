<?php

namespace App\Services\Sefaz;

use App\Models\Issuer;
use App\Services\Xml\XmlIdentifierService;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use NFePHP\Common\Certificate;
use NFePHP\CTe\Common\Standardize;
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
            $this->loadIssuerConfig();
            $this->loadCertificate();

            $this->tools = new Tools(json_encode($this->config), $this->certificate);
            $this->tools->model('57'); // Modelo 57 para CTe
            
            // Desativa contingência conforme CteService original
            $this->tools->contingency->deactivate();

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

    public function downloadCteInBatch(): array
    {
        $allDocuments = [];
        $currentNsu = $this->getLastSavedNsu();
        $initialNsu = $currentNsu;
        $iterations = 0;
        $loopLimit = 50;

        try {
            Log::info('Iniciando download em lote de CTe', [
                'issuer_id' => $this->issuer->id,
                'nsu_inicial' => $currentNsu,
            ]);

            do {
                $iterations++;

                if ($iterations > $loopLimit) {
                    break;
                }

                $result = $this->downloadCteByUltNsu($currentNsu);

                if (! empty($result['documentos'])) {
                    $allDocuments = array_merge($allDocuments, $result['documentos']);
                }

                if ($result['ultNSU'] && $result['maxNSU'] && (int) $result['ultNSU'] == (int) $result['maxNSU']) {
                    $currentNsu = $result['ultNSU'];
                    break;
                }

                if ($result['ultNSU'] && $result['ultNSU'] !== $currentNsu) {
                    $currentNsu = $result['ultNSU'];
                } else {
                    break;
                }

                if (empty($result['documentos'])) {
                    break;
                }

                sleep(2);
            } while (true);

            return [
                'documentos' => $allDocuments,
                'total_documentos' => count($allDocuments),
                'iterations' => $iterations,
                'ultimo_nsu' => $currentNsu,
            ];
        } catch (Exception $e) {
            Log::error('Erro no download em lote de CTe', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha no download em lote: '.$e->getMessage());
        }
    }

    private function downloadCteByUltNsu(?string $ultNsu = null): array
    {
        $currentNsu = $ultNsu ?: $this->getLastSavedNsu();
        $response = $this->tools->sefazDistDFe($currentNsu);
        $result = $this->processDistDFeResponse($response);

        if ($result['ultNSU']) {
            $this->saveLastNsu((int) $result['ultNSU']);
        }

        return $result;
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

        $cStat = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
        $xMotivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        $ultNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue ?? null;
        $maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue ?? null;
        $lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);

        $result = [
            'status' => $cStat,
            'motivo' => $xMotivo,
            'ultNSU' => $ultNSU,
            'maxNSU' => $maxNSU,
            'documentos' => [],
        ];

        if ($cStat == '138' && ! empty($lote)) {
            $result['documentos'] = $this->extractDocumentsFromLote($lote, $maxNSU);
        }

        return $result;
    }

    private function extractDocumentsFromLote(\DOMElement $lote, ?string $maxNSU = null): array
    {
        $documentos = [];
        $docs = $lote->getElementsByTagName('docZip');

        foreach ($docs as $doc) {
            $nsu = $doc->getAttribute('NSU');
            $schema = $doc->getAttribute('schema');
            $contentZipped = $doc->nodeValue;

            if ($contentZipped) {
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
                }
            }
        }

        return $documentos;
    }

    public function sefazManifesta(string $chCTe, string $tpEvento, string $xJust, int $nSeqEvento, string $uf): string
    {
        return $this->tools->sefazManifesta($chCTe, $tpEvento, $xJust, $nSeqEvento, $uf);
    }
}
