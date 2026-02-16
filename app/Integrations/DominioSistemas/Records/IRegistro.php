<?php

namespace App\Integrations\DominioSistemas\Records;

/**
 * Interface que define o contrato mínimo para qualquer tipo de registro
 */
interface IRegistro
{
    /**
     * Retorna o tipo de registro (ex: 0000, 0010, 0100, etc.)
     *
     * @return string
     */
    public function getTipoRegistro(): string;

    /**
     * Converte o registro para uma linha no formato TXT
     *
     * @return string
     */
    public function converterParaLinhaTxt(): string;

    /**
     * Valida se o registro está em conformidade com o layout
     *
     * @return bool
     */
    public function isValid(): bool;
}