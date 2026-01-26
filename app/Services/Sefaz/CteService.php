<?php

namespace App\Services\Sefaz;

use Exception;
use NFePHP\CTe\Tools;
use App\Models\Issuer;
use App\Traits\HasXmlReader;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\Log;
use App\Services\Sefaz\Traits\HasCte;
use Illuminate\Support\Facades\Crypt;
use App\Services\Sefaz\Traits\HasLogSefaz;
use App\Jobs\Sefaz\Process\ProcessResponseCteSefazJob;

class CteService
{
    use HasCte, HasLogSefaz, HasXmlReader {
        HasCte::formatIsoDateTime insteadof HasLogSefaz;
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
            'cnpj' => $this->issuer->cnpj,
            'schemes' => 'PL_CTe_400',
            'versao' => '4.00',
            'siglaUF' => $this->issuer->municipio()->first()?->sigla ?? 'SP',
        ];


        $certficado_content = Crypt::decrypt($this->issuer->certificado_content);
        $certificado = Certificate::readPfx($certficado_content, Crypt::decrypt($this->issuer->senha_certificado));

        $this->tools = new Tools(json_encode($config), $certificado);
        $contingencia = $this->tools->contingency->deactivate();
        $this->tools->contingency->load($contingencia);
    }

    public function buscarDocumentosFiscaisPorNsu($nsu, $origem = 'SEFAZ')
    {
        $this->sefaz();

        try {
            //executa a busca pelos documentos
            $response = $this->tools->sefazDistDFe(0, intval($nsu));

            Log::info('Log de consulta CTE - SEFAZ - registro - ' . explode(':', $this->issuer->razao_social)[0] . ' : ' . $response);
        } catch (Exception $e) {
            Log::error('Log de consulta a SEFAZ CTe - retorno com problema - ' . $e->getMessage() . ' Empresa: ' . explode(':', $this->issuer->razao_social)[0]);

            return;
        }

        $reader = loadXmlReader($response);

        if ($this->checkEmptyOrError($reader)) {
            return;
        }

        //Processa o lote
        ProcessResponseCteSefazJob::dispatch($this->issuer, $response, $origem)->onQueue('high');
    }

    public function buscarDocumentosFiscais($origem = 'SEFAZ')
    {
        $this->sefaz();

        $ultNSU = $this->issuer->ult_nsu_cte;
        $maxNSU = $ultNSU;
        $loopLimit = 50;
        $iCount = 0;

        while ($ultNSU <= $maxNSU) {
            $iCount++;

            if ($iCount >= $loopLimit) {
                //o limite de loops foi atingido pare de consultar
                break;
            }

            try {
                //executa a busca pelos documentos
                $response = $this->tools->sefazDistDFe($ultNSU, 0);
                Log::info('Log de consulta CTE - SEFAZ - registro - ' . explode(':', $this->issuer->razao_social)[0] . ' : ' . $response);
            } catch (Exception $e) {
                Log::error('Log de consulta a SEFAZ CTe - retorno com problema - ' . $e->getMessage() . ' Empresa: ' . explode(':', $this->issuer->razao_social)[0]);

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
                        'ult_nsu_cte' => $ultNSU,
                        'ultima_consulta_cte' => date('Y-m-d H:i:s'),
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
            ProcessResponseCteSefazJob::dispatch($this->issuer, $response, $origem)->onQueue('high');

            if ($ultNSU == $maxNSU) {

                $this->issuer->update([
                    'ult_nsu_cte' => $maxNSU,
                    'ultima_consulta_cte' => date('Y-m-d H:i:s'),
                ]);

                //quando o numero máximo de NSU foi atingido não existem mais dados a buscar
                //nesse caso a proxima busca deve ser no minimo após mais uma hora
                //Interrompe para não deixar seu loop infinito !!
                break;
            }

            sleep(8);
        }
    }

    public function sefazManifesta($chCte, $tpEvento, $xJust, $nSeqEvento, $uf)
    {
        $this->sefaz();

        $response = $this->tools->sefazManifesta($chCte, $tpEvento, $xJust, $nSeqEvento,  $uf);


        return $response;
    }
}
