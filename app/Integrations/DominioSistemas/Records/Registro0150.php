<?php

namespace App\Integrations\DominioSistemas\Records;

/**
 * Registro 0150 - Produtos - Unidade de Medida
 * Produtos - Unidade de medida.
 *
 * Campos:
 * 1 - Identificação do registro (fixo: 0150)
 * 2 - Sigla
 * 3 - Descrição
 */
class Registro0150 extends RegistroBase
{
    private string $sigla;

    private string $descricao;

    public function __construct(
        string $sigla,
        string $descricao
    ) {
        $this->sigla = $sigla;
        $this->descricao = $descricao;
    }

    public function getTipoRegistro(): string
    {
        return '0150';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação do registro
            $this->formatarCampo($this->sigla, null, 'C'), // Campo 2: Sigla
            $this->formatarCampo($this->descricao, null, 'C'), // Campo 3: Descrição
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 0150
        return ! empty($this->sigla) && ! empty($this->descricao);
    }

    // Getters
    public function getSigla(): string
    {
        return $this->sigla;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    // Setters
    public function setSigla(string $sigla): void
    {
        $this->sigla = $sigla;
    }

    public function setDescricao(string $descricao): void
    {
        $this->descricao = $descricao;
    }
}
