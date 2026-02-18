<?php

namespace App\Integrations\DominioSistemas\Records;

use DateTime;

/**
 * Registro 0135 - Produtos - Valor Unitário
 * Produtos - Valor Unitário. Este é um registro filho do registro 0100.
 * 
 * Campos:
 * 1 - Identificação do registro (fixo: 0135)
 * 2 - Data (primeiro dia do mês)
 * 3 - Valor Unitário (com 6 casas decimais)
 */
class Registro0135 extends RegistroBase
{
    private DateTime $data;
    private float $valorUnitario;

    public function __construct(
        DateTime $data,
        float $valorUnitario
    ) {
        // Converte a data para o primeiro dia do mês
        $this->data = $data->setDate((int)$data->format('Y'), (int)$data->format('m'), 1);
        $this->valorUnitario = $valorUnitario;
    }

    public function getTipoRegistro(): string
    {
        return '0135';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação do registro
            $this->formatarCampo($this->data->format('d/m/Y'), null, 'X'), // Campo 2: Data (dd/mm/aaaa)
            $this->formatarCampo($this->valorUnitario, null, 'D6'), // Campo 3: Valor Unitário (6 casas decimais)
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 0135
        return !empty($this->data) && $this->valorUnitario >= 0;
    }

    // Getters
    public function getData(): DateTime
    {
        return $this->data;
    }

    public function getValorUnitario(): float
    {
        return $this->valorUnitario;
    }

    // Setters
    public function setData(DateTime $data): void   
    {
        $this->data = $data;
    }

    public function setValorUnitario(float $valorUnitario): void
    {
        $this->valorUnitario = $valorUnitario;
    }
}
