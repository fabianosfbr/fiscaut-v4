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
            throw new Exception('Falha na inicialização do serviço: '.$e->getMessage());
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
            throw new Exception('Falha ao carregar certificado digital: '.$e->getMessage());
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
                    Log::warning('Limite de iterações atingido no download em lote', [
                        'issuer_id' => $this->issuer->id,
                        'iterations' => $iterations,
                    ]);
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
            throw new Exception('Falha no download em lote: '.$e->getMessage());
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

            Log::info('Iniciando download de NFe por NSU', [
                'issuer_id' => $this->issuer->id,
                'nsu_utilizado' => $currentNsu,
            ]);

            $response = $this->shouldMockDistDFe()
                ? $this->getMockDistDFeResponse()
                : $this->getTools()->sefazDistDFe($currentNsu);

            if (false) {
           $response = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><nfeDistDFeInteresseResponse xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe"><nfeDistDFeInteresseResult><retDistDFeInt xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versao="1.01" xmlns="http://www.portalfiscal.inf.br/nfe"><tpAmb>1</tpAmb><verAplic>1.7.6</verAplic><cStat>138</cStat><xMotivo>Documento(s) localizado(s)</xMotivo><dhResp>2026-01-26T23:59:59-03:00</dhResp><ultNSU>000000000487402</ultNSU><maxNSU>000000000487402</maxNSU><loteDistDFeInt><docZip NSU="000000000487383" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRT2/CMAzFv0rVexs7adM/MpUmNg7TYGhw2LWUQCtBwtKMon36FdZV24nb89PP9pNNVrVPZ6Wd8S7Hg27zS7ud+LVzp5yxruvCToTG7hkHQPY+f1lVtTqW/gg39+Gg0a0rdaV876xsW5qJjyHgMONf/8lYVx52TVuVh7DRu3Bjmd4pv6Dq1e5LU2RIbJA0XSyfCyFkKhAR+pWQELuZVNWLmSpEzGXvpiJOIimuBCZxfAUBOKaQpggRj6M4yWQ/9tZD2/rnHAUHLgPAgMs15zlEOSYBiByA2MiQOw1KIkiMiI0G6ZX6GHQf+k9Fl0HMH2eB8h4+nbHNV7k1XmWO3nQdKGK/SB/nTVWbe3EGhvTSGlekGXKJHJIsyuIk7ZffbGLjr4tvK18HSfcBAAA=</docZip><docZip NSU="000000000487384" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIVRQW7CMBD8SpR74rWdOCEylipUDlVLEXDo1QRDIoGdOi5B/U4PfQgfq4E0ak/cZsczs2ObW9U+HpV2Jjgd9rotTu1mHFbONQVCXdfFHY2N3SECgNHby/OyrNRBhoO4vi+Oat06qUsVBkdlW2nGIY4B9xn//I2xTu63dVvKfVzrbby2SG9VKHj5anfSiBHmqId8Mps/CUpZTjHG4FdCxtGV5GU1mypBU8I8m9M0Sxi9KHCWphchAME5Tpm3UD/SjPrYq4dvqttzCAKERYAjwlaEFDgpII2AFgAcDRrumh75KHY5Ggiul+q9x770n4mferBQu7p11gQbFTx8OGPrT3n+Pn9dickqUkEjrQxkMJtGiqNfm6+4UOX6XsVew/XcGifyESYME8iTBJLEX/dGczT8v/gBnEy06gsCAAA=</docZip><docZip NSU="000000000487385" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW7CMBBFrxJln9hjJyZExlKFyqJqKQIW3ZpgSCSwU8clqNfpogfhYjUhjdoVuz9/3oy/bW5V83hS2pngfDzoJj8320lYOlfnCLVtG7c0NnaPCMaA3l6eV0WpjjIc4Oo+HFW6cVIXKgxOyjbSTEKIMfQ7/s3Xxjp52FVNIQ9xpXfxxiK9U6HgxavdSyPGwFEv+XS+eBKUsowCAPZH4hFHncmLcj5TgqaEeTej6Shh9ErAKE2vIMYEMkgy30zHaQqM+rXdDN+Wt+cQBBMWYYgIWxOSQ5JjX9IcY44Ghru6Vwwwu7YGg+uVeu+1D/2n4udeLNW+apw1wVYFDx/O2OpTXr4vX50xXUcqqKWVgQzms0hx9DvmIy5VsbkXsWe4XljjRDYGwoDgLEn81ZkP1NkcDf8vfgD8GqSMCwIAAA==</docZip><docZip NSU="000000000487386" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW6DMBBFr4LYgz1jMAQ5SFXULKo2jdIsunWIE5ASmxo3RL1OFz1ILlZCKWpX2X1/vz/zZQurmvuT0s545+NBN9m52U790rk6I6Rt27BlobF7gpQCeX16fClKdZT+CFe34aDSjZO6UL53UraRZupDSGGY8S9fG+vkYVc1hTyEld6FG0v0Tvm5KJ7tXpp8AoIMUswWy4ecMZ4yAKDdSpoI0puiKBdzlbMYeeemLE4izq4EJHF8BSlFSAGTLoJRzBiLurF9RmzLn+fIkSIPKATI14gZRBliQFlGqSAjI1w9KA6UX69GQ+gX9TborvSfkzgPYqX2VeOs8bbKu3t3xlYf8vJ1+eyN2TpQXi2t9KS3mAdKkN9YV3Glis2tigMj9NIal6cTQA5I0yjmmPKuUG8LMv5//g0mgguuCwIAAA==</docZip><docZip NSU="000000000487387" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRTW7CMBSErxJln/g9mzg/MpEqVBZVSxGw6NYEQyKBnTouQb1OFz0IF2uANGpX7Mbjb57HtrCqeTwq7Yx3Oux1k52azdgvnaszQtq2DVsWGrsjFADJ28vzsijVQfoDXN2Hg0o3TupC+d5R2UaasY8hYD/jX7421sn9tmoKuQ8rvQ3Xluit8nNRvNqdNHmKgvRSTGbzp5wxnjBEhO5IiAW5mqIoZ1OVs4jyzk1YFI84uxAYR9EFBKCYIE06lSY04jztxl4zYlPeniOnQHkAGFC+ojTDUUZZACwDEGRghKt7xRH4ZWswhF6q9153pf+sxKkXC7WrGmeNt1Hew4cztvqU5+/z19WYrALl1dJKT3qzaaAE+Y11FReqWN+r2DNCz61xeZIi5UghGUUxg+66N1uQ4f/zHxvNCJMLAgAA</docZip><docZip NSU="000000000487388" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW7CMBBFrxJln3jGJk6ITKQKlUXVUkRZdGuCIZHATh0XUK/TRQ/CxWrSNGpX7L6/35/5soVV7f1RaWeC82Gv2/zcbiZh5VyTE3I6neITi43dEQqA5PXp8aWs1EGGA1zfhqNat07qUoXBUdlWmkmIMWA/41++MdbJ/bZuS7mPa72N15borQoLUT7bnTTFGAXppZjOFw8FYzxjiAh+JaSCdKYoq/lMFSyh3LsZS9IRZ1cC0yS5ggAUM6Q+lrA0S3gCfmyXEZvq5zkKCpRHgBHlK0pzHOU0jYDl4NGBEa7pFUfg16vBEPpFvfXal/5zEudeLNWubp01wUYFd+/O2PpDXr4un50xXUUqaKSVgQzms0gJ8hvzFZeqXN+q2DNCL6xxRTZGypFCNuIUOPWFOluQ4f+LbyCfZSQLAgAA</docZip><docZip NSU="000000000487389" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWR0U7CMBiFX2XZ/db+7VrKUpYYIhdGkQAX3pZR2BJoZ1cZ8XW88EF4McecUxMT705Pv/P35K90ur49aeNtcD4eTJ2e6+0kLLyvUoSapokbGlu3RwRjQE8P96u80EcVDnD5PxyVpvbK5DoMTtrVyk5CiDH0M37lK+u8OuzKOleHuDS7eOOQ2ekwk/mj2yubjUGiXsrpfHGXUcoFBQDcPolHEnWmzIv5TGeUEd66grJRwumVgBFjVxBjAgISBnicMEaBsnZsl5Hb4nMdGcGERxgiwteEpJCkiYgwTTGWaGCkr3rFAfPr1WBIs9LPvW5L/zjJcy+Wel/W3tlgq4ObF29d+aou75e3zpiuIx1UyqlABfNZpCX6irUVlzrf/Flx/F2xZ6RZOOszMQbCgWCRtOsSoi3U2RIN/599AGPg428LAgAA</docZip><docZip NSU="000000000487390" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW7CMBRErxJln9jfThwTmUgVKouqpQhYdGuCIZHATh2XoF6nix6Ei9VAGrUrdvPHb/xHtrCqfTwq7UxwOux1m5/azTisnGtyhLquizsaG7tDBGNAby/Py7JSBxkOcH0fjmrdOqlLFQZHZVtpxiHEGPo7/uUbY53cb+u2lPu41tt4bZHeqrAQ5avdSVOMQKBeisls/lRQyjgFAOxX4kygqynKajZVBU0J8y6naZYweiEgS9MLiDEBDtQryHiSJdQnbxmxqW7PURBMWIQhImxFSA5JnvqR5hgLNDDCNb1igNnlaDCEXqr3XvvSfyZx6sVC7erWWRNsVPDw4YytP+X5+/x1NSarSAWNtDKQwWwaKYF+Y77iQpXrexV7Rui5Na7gIyAMCOYJHzFIfKGrLdDw/8UPNTq8LwsCAAA=</docZip><docZip NSU="000000000487391" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW7CMBRErxJln/h/mxgTmUgVKouqpYiy6NYEQyKBnTouQb1OFz0IF2tI06hdsbHG4zffI1s6Xd+ftPE2OB8Ppk7P9XYaFt5XKSFN08QNi63bEwqA5PXp8SUv9FGFA1zehqPS1F6ZXIfBSbta2WmIMWA/41++ss6rw66sc3WIS7OLN46YnQ4zmT+7vbLZBCXppZwtlg8ZY1wwRIT2ShhL0pkyLxZznbGE8tYVLBmPOLsSOE6SKwhAUSAbIVCBgiKbtGO7jNwWP8+RUaA8AowoX1OaYpKCiIClAJIMjPRVrzgCvx4NhjQv+q3Xbek/O3nuxUrvy9o7G2x1cPfurSs/1OXr8tkZs3Wkg0o5FahgMY+0JL+xtuJK55tbFXtGmqWzPhMTpBwpiATaZdQW6mxJhv/PvgFXHU/pCwIAAA==</docZip><docZip NSU="000000000487392" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW7CMBRErxJln9jfxk6ITKQKlUXVUgQsujXBkEhgp45LUK/TRQ/CxWpCGrUrduPxm/9HtrCqeTwp7UxwPh50k52b7SQsnaszhNq2jVsaG7tHBGNAby/Pq6JURxkOcHUfjirdOKkLFQYnZRtpJiHEGPoZ//K1sU4edlVTyENc6V28sUjvVJiL4tXupcnHIFAvxXS+eMop5SkFAOxX4kSgzhRFOZ+pnDLCvZtSlow4vRKQMHYFMSaQAiNeMQY8TbAf22XEtrw9R04w4RGGiPA1IRmwjLAI0wx7dGCEq3vFAfPr1WAIvVLvvfal/5zEuRdLta8aZ02wVcHDhzO2+pSX78tXZ0zXkQpqaWUgg/ksUgL9xnzFpSo29yr2jNALa1yejoFwIDhlkFI68oU6W6Dh//MffSspoQsCAAA=</docZip><docZip NSU="000000000487393" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRTW7CMBSErxJln9jPxs6PjKUKlUXVUkRZdGuCIZHATh2XoF6nix6EizWkadSu2I3H37w3soXTzf1JG2+D8/FgmvzcbKdh6X2dI9S2bdzS2Lo9IhgDen16fClKfVThCFe34agyjVem0GFw0q5RdhpCjGGY8S9fW+fVYVc1hTrEldnFG4fMTodSFM9ur6zMQKBBitli+SAp5SkFANytxIlAvSmKcjHXkjLCOzelLJlweiUgYewKYkwghQkHPElokkDGu7F9RmzLn+eQBBMeYYgIXxOSA8spiTDNMRZoZISvB9WN4ter0RDmRb8Nuiv95yTOg1jpfdV4Z4OtDu7evXXVh7p8XT57Y7aOdFArpwIVLOaRFug31lVc6WJzq+LACLN01ss0A8KB4JQRxjLaFeptgcb/l9/edwZbCwIAAA==</docZip><docZip NSU="000000000487394" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW7CMBBFrxJln3jGJiaJTKQKlUXVUkRZdGuCIZHATh2XoF6nix6Ei9XQNGpX7L6/35/5soVV7f1RaWeC02Gv2/zUbiZh5VyTE9J1Xdyx2NgdoQBIXp8eX8pKHWQ4wPVtOKp166QuVRgclW2lmYQYA/Yz/uUbY53cb+u2lPu41tt4bYneqrAQ5bPdSVNkKEgvxXS+eCgY4ylDRPArYSzI1RRlNZ+pgiWUezdlyXjE2YXAcZJcQACKqQ8hZAlNE6DMj71mxKb6eY6CAuURYET5itIcs9xrYDmAIAMjXNMrjsAvV4Mh9It667Uv/eckTr1Yql3dOmuCjQru3p2x9Yc8f50/r8Z0FamgkVYGMpjPIiXIb8xXXKpyfatizwi9sMYVaYaUI4WUZ6MEqS90tQUZ/r/4Bsd3iaULAgAA</docZip><docZip NSU="000000000487395" schema="procEventoNFe_v1.00.xsd">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</docZip><docZip NSU="000000000487396" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRTW7CMBCFrxJln3jGJs6PTKQKlUXVUgQsujXBkEhgp45LUK/TRQ/CxRpoiNoVu+fn78082cKq5vGotDPe6bDXTXZqNmO/dK7OCGnbNmxZaOyOUAAkby/Py6JUB+kPcHUfDirdOKkL5XtHZRtpxj6GgP2Mf/naWCf326op5D6s9DZcW6K3ys9F8Wp30uQpCtJLMZnNn3LGeMIQEbqVEAtyNUVRzqYqZxHlnZuwKB5xdiEwjqILCEAxQUwRRmnMEoSoG3vNiE35+xw5BcoDwIDyFaUZoxllAbAMQJCBEa7uFUfgl6vBEHqp3m+jBPlzEqdeLNSuapw13kZ5Dx/O2OpTnr/PX1djsgqUV0srPenNpoES5BbrKi5Usb5XsWeEnlvj8iRFypFCyngMI9YVutqCDP+f/wAO9qNtCwIAAA==</docZip><docZip NSU="000000000487397" schema="procNFe_v4.00.xsd">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</docZip><docZip NSU="000000000487398" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRQW7CMBREr2Jln9jfJiaJTKQKlUXVUgQsujXBkEhgp7ZLUK/TRQ/CxRpCGrUrduPxm/9HtrDKPZ6U9gadjwftsrPbToLS+zrDuGmaqGGRsXtMCQH89vK8Kkp1lMEAV/fhsNLOS12oAJ2UddJMAogI9DP+5WtjvTzsKlfIQ1TpXbSxWO9UkIvi1e6lyVMQuJdiOl885YzxhAEAaVeSscCdKYpyPlM5iylv3YTF4xFnVwLGcXwFCaGQAIu7WBIDj9uxXUZsy9tz5JRQHhIIKV9Tmo1oxiAkLCNE4IERvu4VB8KvV4Mh9Eq997ot/eckzr1Yqn3lvDVoq9DDhze2+pSX78tXZ0zXoUK1tBJJNJ+FSuDfWFtxqYrNvYo9I/TCGp8nKVAOlKRJmlKWtoU6W+Dh//MfKZF2MQsCAAA=</docZip><docZip NSU="000000000487399" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRTW7CMBCFrxJln3jGJs6PTKQKlUXVUkRZdGuCIZHATh2XoF6nix6EizWkadSu2D0/f2/8NBZWNfcnpZ3xzseDbrJzs536pXN1RkjbtmHLQmP3hAIgeX16fClKdZT+CFe34aDSjZO6UL53UraRZupjCDjM+JevjXXysKuaQh7CSu/CjSV6p/xcFM92L02eoiCDFLPF8iFnjCcMEaF7EmJBelMU5WKuchZR3rkJi+IJZ1cC4yi6ggAUE4gnCFEaYUoT1o3tM2Jb/qwjp0B5ABhQvqY0m9CMpQGwDECQkRGuHhRH4Ner0RD6Rb0Nuiv95yTOg1ipfdU4a7yt8u7enbHVh7x8XT57Y7YOlFdLKz3pLeaBEuQ31lVcqWJzq+LACL20xuVJipQjhTSFuNtXV6i3BRn/P/8G/LZ50AsCAAA=</docZip><docZip NSU="000000000487400" schema="procEventoNFe_v1.00.xsd">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</docZip><docZip NSU="000000000487401" schema="procEventoNFe_v1.00.xsd">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</docZip><docZip NSU="000000000487402" schema="resEvento_v1.01.xsd">H4sIAAAAAAAEAIWRUW+CMBSF/wrhHXpvCxXJlWRx82GZzkwf9opYhURbVzox+/VDRdySJXtpTk++e3pyS1bVT0elnfFO+52u01O9Hvmlc4eUsaZpwkaExm4ZB0D2Pn1ZFKXa534PV//DQaVrl+tC+d5R2To3Ix9DwC7j1/zBWJfvNlVd5Luw0ptwZZneKD+j4tVuc5MNkVgnaTybP2dCyEQgIrRPwoDYxaSinE1UJmIuWzcR8SCS4kzgII7PIADHBCDB1ooRkyhpYy8ztC6v68g4cBkABlwuuUiFTAECEO1JrGfIHTolESRGxHqD9EJ93KKI/bjRqRPTx0mgvIdPZ2z1la+NV5i9N14GitgNaeu8qWL1Zx281+kY0nNrXJYMkUvkyCMpUbZLudrE+r/OvgGo1TXe9wEAAA==</docZip></loteDistDFeInt></retDistDFeInt></nfeDistDFeInteresseResult></nfeDistDFeInteresseResponse></soap:Body></soap:Envelope>';
            }

            // Processa a resposta
            $result = $this->processDistDFeResponse($response);
            
            // SEMPRE atualiza o NSU da empresa em consultas em lote
            if ($result['ultNSU']) {
                $this->saveLastNsu((int) $result['ultNSU']);
                Log::info('NSU da empresa atualizado', [
                    'issuer_id' => $this->issuer->id,
                    'novo_nsu' => $result['ultNSU'],
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Erro no download de NFe por NSU (consulta em lote)', [
                'issuer_id' => $this->issuer->id,
                'ult_nsu' => $currentNsu ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Falha no download de NFe: '.$e->getMessage());
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
            throw new Exception('Falha ao processar resposta: '.$e->getMessage());
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
            throw new Exception('Falha ao verificar status SEFAZ: '.$e->getMessage());
        }
    }
 
    /**
     * Realiza a manifestação de uma NF-e.
     */
    public function sefazManifesta(string $chNFe, string $tpEvento, string $xJust = '', int $nSeqEvento = 1): string
    {
        return $this->getTools()->sefazManifesta($chNFe, $tpEvento, $xJust, $nSeqEvento);
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
