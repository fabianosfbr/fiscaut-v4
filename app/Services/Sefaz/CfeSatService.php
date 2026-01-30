<?php

namespace App\Services\Sefaz;

use App\Models\CupomFiscalEletronico;
use App\Models\Issuer;

class CfeSatService
{
    private static $url = 'https://wssatsp.fazenda.sp.gov.br/CfeConsultarLotes/CfeConsultarLotes.asmx';

    private Issuer $issuer;

    public function issuer($issuer)
    {
        $this->issuer = $issuer;

        return $this;
    }

    public function consulta(string $serie, string $inicial, string $final, string $chave): string
    {
        $dtini = new \DateTime($inicial);
        $dtfim = new \DateTime($final);

        $ini = (string) $dtini->format('dmY').'000000';
        $fim = (string) $dtfim->format('dmY').'235900';

        $satserie = (string) str_pad($serie, 9, '0', STR_PAD_LEFT);

        $message = "<?xml version='1.0' encoding='UTF-8'?>"
            .'<consLote xmlns="http://www.fazenda.sp.gov.br/sat" versao="0.07">'
            ."<nserieSAT>{$satserie}</nserieSAT>"
            ."<dhInicial>{$ini}</dhInicial>"
            ."<dhFinal>{$fim}</dhFinal>"
            ."<chaveSeguranca>{$chave}</chaveSeguranca>"
            .'</consLote>';

        $envelope = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
            .' xmlns:cfec="http://www.fazenda.sp.gov.br/sat/wsdl/CfeConsultaLotes">'
            .'<soapenv:Header>'
            .'<cfec:cfeCabecMsg>'
            .'<cfec:cUF>35</cfec:cUF>'
            .'<cfec:versaoDados>0.07</cfec:versaoDados>'
            .'</cfec:cfeCabecMsg>'
            .'</soapenv:Header>'
            .'<soapenv:Body>'
            .'<cfec:CfeConsultarLotes>'
            .'<cfec:cfeDadosMsg>'
            .htmlentities($message)
            .'</cfec:cfeDadosMsg>'
            .'</cfec:CfeConsultarLotes>'
            .'</soapenv:Body>'
            .'</soapenv:Envelope>';

        // dd($envelope);

        return $this->send($envelope);
    }

    protected function send(string $envelope): string
    {
        $msgSize = strlen($envelope);
        $header = [
            'accept: */*',
            'Accept-Encoding: gzip,deflate',
            'Content-Type: text/xml;charset=UTF-8',
            'SOAPAction: "http://www.fazenda.sp.gov.br/sat/wsdl/CfeConsultar"',
            "Content-length: $msgSize",
        ];

        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_URL, self::$url);
        curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 40);
        curl_setopt($oCurl, CURLOPT_HEADER, 1);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($oCurl);

        $soaperror = curl_error($oCurl);
        $soaperror_code = curl_errno($oCurl);
        $ainfo = curl_getinfo($oCurl);
        if (is_array($ainfo)) {
            $soapinfo = $ainfo;
        }
        $headsize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        // $httpcode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
        curl_close($oCurl);
        // $responseHead = trim(substr($response, 0, $headsize));
        $responseBody = trim(substr($response, $headsize));
        if (! empty($oaperror)) {
            throw new \Exception('Falha de comunicação: '.$soaperror_code.' - '.$soaperror, $soaperror_code);
        }
        $dom = new \DOMDocument;
        $dom->loadXML($responseBody);
        $node = $dom->getElementsByTagName('CfeConsultarLotesResult')->item(0);

        $this->lerXmlCfe($node->textContent);

        return $node->textContent;
    }

    public function exec($content, $origem = 'SEFAZ')
    {
        $xml = simplexml_load_string($content);

        $params = $this->preparaXml($xml);
        $params['origem'] = $origem;
        $params['xml'] = $content;

        // caso não tenha o tenant_id na sessão para serviços rodados via job
        if (! checkTenantId()) {
            $params['tenant_id'] = $this->issuer->tenant_id;
        }

        if (($origem == 'Importacao') || ($origem == 'SIEG')) {
            $cfeExiste = CupomFiscalEletronico::where('chave', $xml->infCFe['Id'])->first();
            if (! isset($cfeExiste)) {
                CupomFiscalEletronico::create($params);
            } else {
                $cfeExiste->update($params);
            }
        } else {
            $lotes = $xml->Lote;
            foreach ($lotes as $lote) {
                $infCfe = $lote->InfCfe->Cfe;
                $cfeExiste = CupomFiscalEletronico::where('chave', $infCfe->Chave)->first();
                if (! isset($cfeExiste)) {
                    CupomFiscalEletronico::create([
                        'chave' => $infCfe->Chave,
                        'emitente_razao_social' => $infCfe->emit->xNome,
                        'emitente_cnpj' => $infCfe->emit->CNPJ,
                        'nCupom' => $infCfe->nCupom,
                        'situacao' => $infCfe->Situacao,
                        'data_emissao' => $this->dateCoverter($infCfe),
                        'origem' => $origem,
                        'vCFe' => $infCfe->vCFe,

                    ]);
                }
            }
        }
    }

    private function preparaXml($xml)
    {
        return [
            'chave' => $xml->infCFe['Id'],
            'emitente_razao_social' => $xml->infCFe->emit->xNome,
            'emitente_cnpj' => $xml->infCFe->emit->CNPJ,
            'nCupom' => $xml->infCFe->ide->nCFe,
            'situacao' => $xml->infCfe->Situacao ?? null,
            'data_emissao' => $this->dateCoverter($xml->infCFe->ide),
            'vCFe' => $xml->infCFe->total->vCFe,
        ];
    }

    private function dateCoverter($infCfe)
    {

        $ano = substr($infCfe->dEmi->__toString(), 0, 4);
        $mes = substr($infCfe->dEmi->__toString(), 4, 2);
        $dia = substr($infCfe->dEmi->__toString(), 6, 2);

        $hora = substr($infCfe->hEmi->__toString(), 0, 2);
        $minuto = substr($infCfe->hEmi->__toString(), 2, 2);
        $segundo = substr($infCfe->hEmi->__toString(), 4, 2);

        // dump($ano . '-'. $mes. '-'.$dia .' '. $hora . ':'. $minuto. ':'.$segundo);

        return new \DateTime($ano.'-'.$mes.'-'.$dia.' '.$hora.':'.$minuto.':'.$segundo);
    }
}
