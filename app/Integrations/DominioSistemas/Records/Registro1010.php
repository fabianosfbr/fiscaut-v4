<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\NotaFiscalEletronica;

class Registro1010 extends RegistroBase
{
    private ?string $informacaoComplementar;

    public function __construct(NotaFiscalEletronica $notaFiscal)
    {
        $xmlData = $this->extrairDadosDoXml($notaFiscal, [
            'infAdic' => ['infAdic'],
        ]);
        $this->informacaoComplementar = isset($xmlData['infAdic']['infCpl'])
            ? str_replace('|', '-', $xmlData['infAdic']['infCpl'])
            : null;
    }

    public function getTipoRegistro(): string
    {
        return '1010';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(),
            '2',
            $this->formatarCampo($this->informacaoComplementar, null, 'C'),
        ];

        return $this->montarLinha($campos);
    }
}
