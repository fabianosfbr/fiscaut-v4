<?php

namespace App\Services\Sefaz\Traits;

use NFePHP\Common\Certificate;
use stdClass;

trait HasCertifiate
{
    public static function lerCertificado($arquivo, $senha)
    {
        $retorno = new stdClass();
        try {
            $detalhe = Certificate::readPfx($arquivo, $senha);

            $certificado = new stdClass();
            $certificado->inicio = $detalhe->getValidFrom();
            $certificado->validade = $detalhe->getValidTo();
            $certificado->doc = $detalhe->getCnpj() ?? $detalhe->getCpf();
            $certificado->company = $detalhe->getCompanyName();

            $retorno->is_error = false;
            $retorno->title = 'Certificado Digital';
            $retorno->error = '';
            $retorno->payload = $certificado;
        } catch (\Throwable $e) {

            $retorno->is_error = true;
            $retorno->title = 'Erro ao ler certificado digital';
            $retorno->error = $e->getMessage();
            $retorno->payload = '';
        }

        return $retorno;
    }
}
