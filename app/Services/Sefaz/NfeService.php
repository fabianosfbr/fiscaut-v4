<?php

namespace App\Services\Sefaz;

use Exception;
use NFePHP\NFe\Tools;
use App\Models\Issuer;
use App\Traits\HasXmlReader;
use NFePHP\Common\Certificate;
use App\Models\LogSefazResumoNfe;
use NFePHP\NFe\Common\Standardize;
use Illuminate\Support\Facades\Log;
use App\Services\Sefaz\Traits\HasNfe;
use Illuminate\Support\Facades\Crypt;
use App\Models\LogSefazManifestoEvent;
use App\Services\Sefaz\Traits\HasLogSefaz;
use App\Services\Sefaz\Traits\HasCertifiate;
use App\Jobs\Sefaz\Process\ProcessResponseNfeSefazJob;

class NfeService
{
    use HasCertifiate, HasLogSefaz, HasNfe, HasXmlReader {
        HasNfe::formatIsoDateTime insteadof HasLogSefaz;
    }

    private Tools $tools;

    private Issuer $issuer;

    public function issuer($issuer)
    {
        $this->issuer = $issuer;

        return $this;
    }

    private function sefaz()
    {
        $config = [
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

        $certficado_content = Crypt::decrypt($this->issuer->certificado_content);
        $certificado = Certificate::readPfx($certficado_content, Crypt::decrypt($this->issuer->senha_certificado));

        $this->tools = new Tools(json_encode($config), $certificado);

        $this->tools->model('55');
        //este serviço somente opera em ambiente de produção 1 - produção 2-homoloação
        $this->tools->setEnvironment(config('admin.environment.HAMBIENTE_SEFAZ'));
    }

    public function buscarDocumentosFiscaisPorNsu($nsu, $origem = 'SEFAZ')
    {

        $this->sefaz();

        try {
            //executa a busca pelos documentos
            $response = $this->tools->sefazDistDFe(0, intval($nsu));

            Log::info('Log de consulta NFe - SEFAZ - registro - ' . explode(':', $this->issuer->razao_social)[0] . ' : ' . $response);
        } catch (Exception $e) {
            Log::error('Log de consulta a SEFAZ NFe - retorno com problema - ' . $e->getMessage() . ' Empresa: ' . explode(':', $this->issuer->razao_social)[0]);

            return;
        }

        $reader = loadXmlReader($response);

        if ($this->checkEmptyOrError($reader)) {
            return;
        }

        //Processa o lote
        ProcessResponseNfeSefazJob::dispatch($this->issuer, $response, $origem)->onQueue('high');
    }

    public function buscarDocumentosFiscaisString($response)
    {

        //  $this->sefaz();

        try {

            Log::info('Log de consulta NFe - SEFAZ - registro - ' . explode(':', $this->issuer->razao_social)[0] . ' : ' . $response);
        } catch (Exception $e) {
            Log::error('Log de consulta a SEFAZ NFe - retorno com problema - ' . $e->getMessage() . ' Empresa: ' . explode(':', $this->issuer->razao_social)[0]);

            return;
        }

        $reader = loadXmlReader($response);
        $root = $reader['retDistDFeInt'] ?? [];

        $ultNSU = intval($root['ultNSU'] ?? 0);
        $maxNSU = intval($root['maxNSU'] ?? 0);

        $docZipList = xml_list($root['loteDistDFeInt']['docZip'] ?? null);

        foreach ($docZipList as $docZip) {
            $content = $docZip['@content'] ?? '';
            $xml = gzdecode(base64_decode($content));

            if ($xml === false) {
                continue;
            }

            $xmlReader = loadXmlReader($xml);

            if (isset($xmlReader['resNFe'])) {
                $this->exec($xmlReader, $xml, 'Importacao');
            }
        }
    }

    public function buscarDocumentosFiscais($origem = 'SEFAZ')
    {
        $this->sefaz();

        $ultNSU = $this->issuer->ult_nsu_nfe;
        $maxNSU = $ultNSU;
        $loopLimit = 50;
        $iCount = 0;

        while ($ultNSU <= $maxNSU) {

            //Cada loop gera um job para ser executado

            $iCount++;

            if ($iCount >= $loopLimit) {
                //o limite de loops foi atingido pare de consultar
                break;
            }

            try {
                //executa a busca pelos documentos
                $response = $this->tools->sefazDistDFe($ultNSU, 0);

                Log::info('Log de consulta NFe - SEFAZ - registro em lote - ' . explode(':', $this->issuer->razao_social)[0] . ' : ' . $response);
            } catch (Exception $e) {
                Log::error('Log de consulta a SEFAZ NFe - retorno com problema - ' . $e->getMessage() . ' Empresa: ' . explode(':', $this->issuer->razao_social)[0]);

                return;
            }

            //extrair e salvar os retornos
            $reader = loadXmlReader($response);
            $root = $reader['retDistDFeInt'] ?? [];

            $ultNSU = intval($root['ultNSU'] ?? 0);
            $maxNSU = intval($root['maxNSU'] ?? 0);

            //Verifica erros ou vazio
            if ($this->checkEmptyOrError($reader)) {

                if (($root['ultNSU'] ?? null) !== null) {
                    $this->issuer->update([
                        'ult_nsu_nfe' => $ultNSU,
                        'ultima_consulta_nfe' => date('Y-m-d H:i:s'),
                    ]);
                }

                return;
            }

            //Não tem docs para processar
            $docZipList = xml_list($root['loteDistDFeInt']['docZip'] ?? null);
            if ($docZipList === []) {
                continue;
            }

            //Processa o lote
            ProcessResponseNfeSefazJob::dispatch($this->issuer, $response, $origem)->onQueue('high');

            if ($ultNSU == $maxNSU) {

                $this->issuer->update([
                    'ult_nsu_nfe' => $maxNSU,
                    'ultima_consulta_nfe' => date('Y-m-d H:i:s'),
                ]);

                //quando o numero máximo de NSU foi atingido não existem mais dados a buscar
                //nesse caso a proxima busca deve ser no minimo após mais uma hora
                //Interrompe para não deixar seu loop infinito !!
                break;
            }

            sleep(8);
        }
    }

    public function sefazManifesta($chNFe, $tpEvento, $xJust = '', $nSeqEvento = 1)
    {
        $this->sefaz();

        $response = $this->tools->sefazManifesta($chNFe, $tpEvento, $xJust, $nSeqEvento = 1);

        return $response;
    }

    public function manifestaCienciaDaOperacao()
    {
        throw_if(!isset($this->issuer), new Exception('É necessário informar a empresa para realizar consulta.'));

        $resumos = LogSefazResumoNfe::where('issuer_id', $this->issuer->id)->where('is_ciente_operacao', false)->get();

        foreach ($resumos as $resumo) {
            $response = $this->sefazManifesta($resumo->chave, '210210'); // código da ciência da operação

            Log::info('Log de manifestação NFe - SEFAZ - registro - ' . explode(':', $this->issuer->razao_social)[0] . ' : ' . $response);
            $st = new Standardize($response);
            $std = $st->toStd();

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
                'data_ciencia_manifesto' => date('y-m-d H:i:s'),
                'is_ciente_operacao' => true,
            ]);

            sleep(2);
        }
    }
}
