<?php

namespace App\Services\Sefaz;

use App\Models\Issuer;
use App\Models\LogSefazNfseEvent;
use App\Models\NotaFiscalServico;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;

class NfseOldService
{
    private Tools $tools;

    private Issuer $issuer;

    public function issuer($issuer)
    {
        $this->issuer = $issuer;

        return $this;
    }

    public function buscarDocumentosFiscaisPorNsu(?int $nsu = null, $origem = 'SEFAZ')
    {
        $issuer = $this->issuer->refresh();

        if ($nsu == null) {
            $nsu = $issuer->ult_nfse_nsu;
        }

        $response = $this->getDistDfe($nsu);

        if ($response->StatusProcessamento != 'DOCUMENTOS_LOCALIZADOS') {
            return;
        }

        foreach ($response->LoteDFe as $DFe) {
            $this->saveDFe($DFe);
            $ultNSU = max($nsu, (int) $DFe->NSU);
            $issuer->ult_nfse_nsu = $ultNSU;
            $issuer->saveQuietly();
        }

        $this->buscarDocumentosFiscaisPorNsu();
    }

    private function getDistDfe($ultNsu = 0)
    {
        $certficado_content = Crypt::decrypt($this->issuer->certificado_content);
        $certificado = Certificate::readPfx($certficado_content, Crypt::decrypt($this->issuer->senha_certificado));

        $cnpjConsulta = $this->issuer->cnpj;

        $url = "https://adn.nfse.gov.br/contribuintes/dfe/$ultNsu?cnpjConsulta=$cnpjConsulta";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT_BLOB, $certificado->publicKey);
        curl_setopt($ch, CURLOPT_SSLKEY_BLOB, $certificado->privateKey);

        $serverResponse = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != 200 && $httpCode != 404) {
            throw new \Exception("Erro na consulta DFe ADN: HTTP Code $httpCode - Error: $error - Response: $serverResponse");
        }

        return json_decode($serverResponse);
    }

    private function saveDFe($DFe)
    {
        $xml = gzdecode(base64_decode($DFe->ArquivoXml));

        // Abre o XML
        $xmlObj = simplexml_load_string($xml);

        if ($DFe->TipoDocumento == 'NFSE') {
            $tomador = $xmlObj->infNFSe->DPS->infDPS->toma;

            $intermediario = $xmlObj->infNFSe->DPS->infDPS->interm;

            if ($intermediario) {
                $tomador = $intermediario;
            }

            $params = [
                'chave_acesso' => $DFe->ChaveAcesso,
                'data_emissao' => Carbon::parse($xmlObj->infNFSe->DPS->infDPS->dhEmi),
                'prestador_cnpj' => (string) ($xmlObj->infNFSe->emit->CNPJ ?? $xmlObj->infNFSe->emit->CPF ?? null),
                'prestador_servico' => (string) ($xmlObj->infNFSe->emit->xNome ?? null),
                'prestador_im' => (string) ($xmlObj->infNFSe->emit->IM ?? null),

                'tomador_cnpj' => (string) ($tomador->CNPJ ?? $tomador->CPF ?? null),
                'tomador_servico' => (string) ($tomador->xNome ?? null),
                'tomador_im' => (string) ($tomador->IM ?? null),

                'numero' => (int) ($xmlObj->infNFSe->nNFSe ?? null),
                'valor_servico' => (float) ($xmlObj->infNFSe->valores->vLiq ?? null),
                'xml_content' => $xml,
                'xml' => $xml,
            ];

            NotaFiscalServico::updateOrCreate([
                'chave_acesso' => $DFe->ChaveAcesso,
            ], $params);
        }

        if ($DFe->TipoDocumento == 'EVENTO') {

            LogSefazNfseEvent::updateOrCreate([
                'chave' => $DFe->ChaveAcesso,
                'c_motivo' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105102->cMotivo ?? null),
            ], [
                'dh_evento' => Carbon::parse($DFe->DataHoraGeracao),
                'x_desc' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105102->xDesc ?? null),
                'c_motivo' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105102->cMotivo ?? null),
                'x_motivo' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105102->xMotivo ?? null),
                'ch_substituta' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105102->chSubstituta ?? null),
                'xml' => $xml,
            ]);
        }
    }

    public function getDanfse(string $chaveAcesso)
    {
        $certficado_content = Crypt::decrypt($this->issuer->certificado_content);
        $certificado = Certificate::readPfx($certficado_content, Crypt::decrypt($this->issuer->senha_certificado));

        $url = "https://adn.nfse.gov.br/danfse/$chaveAcesso";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT_BLOB, $certificado->publicKey);
        curl_setopt($ch, CURLOPT_SSLKEY_BLOB, $certificado->privateKey);

        $serverResponse = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            throw new \Exception("Erro ao gerar Danfse: HTTP Code $httpCode - Error: $error");
        }

        return $serverResponse;
    }
}
