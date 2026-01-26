<?php

namespace App\Services\Sefaz\Traits;


use App\Jobs\Sefaz\CheckNfeData;
use Illuminate\Support\Facades\Log;
use App\Models\NotaFiscalEletronica;
use App\Models\ConhecimentoTransporteEletronico;

trait HasCte
{
    public function exec($xmlReader, $xml, $origem)
    {
        if (isset($xmlReader['cteProc'])) {

            $chave = $xmlReader['cteProc']['protCTe']['infProt']['chCTe'] ?? null;
            Log::info('Registrando/Atualizando CTe no Fiscaut - Chave:  ' . $chave);

            $params = $this->preparaDadosCte($xmlReader);

            $params['xml'] = gzcompress($xml);
            $params['origem'] = $origem;


            $params['tenant_id'] = $this->issuer->tenant_id;


            $cte = ConhecimentoTransporteEletronico::updateOrCreate(
                [
                    'chave' => $params['chave'],
                    'tenant_id' => $this->issuer->tenant_id,
                ],
                $params
            );


            if (!is_null($params['nfe_chave'])) {

                // Disparar evento de verificar NFe associada
                CheckNfeData::dispatch($cte)->onQueue('low');
            }
        }

        if (isset($xmlReader['procEventoCTe']) || isset($xmlReader['eventoCTe']) || isset($xmlReader['evento'])) {

            $this->registerLogCteEvent($this->issuer, $xml, $xmlReader);
        }
    }

    public function preparaDadosCte($element)
    {
        $cteProc = $element['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $ide = $infCte['ide'] ?? [];
        $protInfProt = $cteProc['protCTe']['infProt'] ?? [];
        $emit = $infCte['emit'] ?? [];
        $dest = $infCte['dest'] ?? null;
        $rem = $infCte['rem'] ?? null;
        $dhEmi = $this->formatIsoDateTime($ide['dhEmi'] ?? null);

        return [
            'nCTe' => $ide['nCT'] ?? null,
            'tpCTe' => $ide['tpCTe'] ?? null,
            'status_cte' => $protInfProt['cStat'] ?? null,
            'vCTe' => $infCte['vPrest']['vRec'] ?? null,
            'emitente_razao_social' => $emit['xNome'] ?? null,
            'emitente_cnpj' => $emit['CNPJ'] ?? $emit['CPF'] ?? null,
            'destinatario_razao_social' => is_array($dest) ? ($dest['xNome'] ?? null) : null,
            'destinatario_cnpj' => is_array($dest) ? ($dest['CNPJ'] ?? $dest['CPF'] ?? null) : null,
            'aut_xml' => $this->getAutxml($element),
            'remetente_razao_social' => is_array($rem) ? ($rem['xNome'] ?? null) : null,
            'remetente_cnpj' => is_array($rem) ? ($rem['CNPJ'] ?? $rem['CPF'] ?? null) : null,
            'data_emissao' => $dhEmi,
            'chave' => $protInfProt['chCTe'] ?? null,
            'uf_origem' => $ide['UFIni'] ?? null,
            'xMunIni' => $ide['xMunIni'] ?? null,
            'uf_destino' => $ide['UFFim'] ?? null,
            'xMunFim' => $ide['xMunFim'] ?? null,
            'nProt' => $protInfProt['nProt'] ?? null,
            'nfe_chave' => $this->getNfeChaves($element),
            'tomador_razao_social' => $this->preparaTomador($element, 'xNome'),
            'tomador_cnpj' => $this->preparaTomador($element, 'CNPJ'),
        ];
    }

    private function preparaTomador($element, $attribute)
    {
        $cteProc = $element['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $toma = searchValueInArray($infCte, 'toma');

        switch ((int) $toma) {
            case 0:
                return $infCte['rem'][$attribute] ?? $infCte['rem']['CPF'] ?? null;
            case 1:
                return $infCte['exped'][$attribute] ?? null;
            case 2:
                return $infCte['receb'][$attribute] ?? null;
            case 3:
                return $infCte['dest'][$attribute] ?? $infCte['dest']['CPF'] ?? null;
            case 4:
                return $infCte['toma4'][$attribute] ?? $infCte['toma4']['CPF'] ?? null;
        }

        return null;
    }

    private function getNfeChaves($element)
    {
        $cteProc = $element['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $nfeChaves = xml_list($infCte['infCTeNorm']['infDoc']['infNFe'] ?? null);

        if ($nfeChaves === []) {
            return null;
        }

        return json_encode($nfeChaves);
    }

    private function getAutxml($element)
    {
        $cteProc = $element['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $autXml = $infCte['autXML'] ?? null;

        if ($autXml === null) {
            return null;
        }

        return json_encode(xml_list($autXml));
    }

    public function checkEmptyOrError($element)
    {
        $cStat = $element['retDistDFeInt']['cStat'] ?? null;
        if (in_array($cStat, ['137', '656'])) {
            //137 - Nenhum documento localizado, a SEFAZ está te informando para consultar novamente após uma hora a contar desse momento
            //656 - Consumo Indevido, a SEFAZ bloqueou o seu acesso por uma hora pois as regras de consultas não foram observadas
            //nesses dois casos pare as consultas imediatamente e retome apenas daqui a uma hora, pelo menos !!
            Log::info('Log de consulta CTE - SEFAZ - retorno -  ' . $cStat . ' Empresa: ' . explode(':', $this->issuer->razao_social)[0]);

            return true;
        }

        return false;
    }

    private function formatIsoDateTime(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parts = explode('T', $value, 2);
        if (count($parts) !== 2) {
            return $value;
        }

        $date = $parts[0];
        $time = explode('-', $parts[1], 2)[0];

        return $date . ' ' . $time;
    }
}
