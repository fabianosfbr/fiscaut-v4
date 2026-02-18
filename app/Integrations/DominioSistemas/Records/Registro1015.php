<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\NotaFiscalEletronica;

class Registro1015 extends RegistroBase
{
    private ?string $observacao;

    public function __construct(NotaFiscalEletronica $notaFiscal)
    {
        $xmlData = $this->extrairDadosDoXml($notaFiscal, [
            'infAdic' => ['infAdic'],
        ]);
        $this->observacao = isset($xmlData['infAdic']['infAdFisco'])
            ? str_replace('|', '-', $xmlData['infAdic']['infAdFisco'])
            : null;
    }

    public function getTipoRegistro(): string
    {
        return '1015';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(),
            '1',
            $this->formatarCampo($this->observacao, null, 'C'),
        ];

        return $this->montarLinha($campos);
    }
}
