<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\NotaFiscalEletronica;

/**
 * Registro 1500 - Notas Fiscais de Entrada – Parcelas
 */
class Registro1500 extends RegistroBase
{
    private $parcela;

    private $numeroParcela;

    private $notaFiscal;

    public function __construct(NotaFiscalEletronica $notaFiscal, array $parcela, int $numeroParcela)
    {
        $this->notaFiscal = $notaFiscal;
        $this->parcela = $parcela;
        $this->numeroParcela = $numeroParcela;
    }

    public function getTipoRegistro(): string
    {
        return '1500';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [];
        $campos[] = $this->getTipoRegistro(); // 1
        $campos[] = $this->formatarCampo($this->parcela['dVenc'] ?? '', null, 'X'); // 2: Vencimento
        $campos[] = $this->formatarCampo($this->parcela['vDup'] ?? 0, null, 'D2'); // 3: Valor
        $campos[] = '0,00'; // 4: Alíquota da CRF
        $campos[] = '0,00'; // 5: Valor da CRF
        $campos[] = '0,00'; // 6: Valor da IRRF
        $campos[] = '0,00'; // 7: Valor ISS Retido
        $campos[] = '0,00'; // 8: Valor INSS Retido
        $campos[] = '0,00'; // 9: Valor do FUNRURAL
        $campos[] = '0,00'; // 10: Valor do PIS Retido
        $campos[] = '0,00'; // 11: Valor do COFINS Retido
        $campos[] = '0,00'; // 12: Valor da CSOC Retido
        $campos[] = '0,00'; // 13: Valor do IRRF Pessoa Física
        $campos[] = $this->formatarCampo($this->parcela['nDup'] ?? '', null, 'C'); // 14: Número do Título

        return $this->montarLinha($campos);
    }
}
