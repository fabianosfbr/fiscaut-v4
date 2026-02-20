<?php

namespace App\Integrations\DominioSistemas\Records;

/**
 * Registro 0000 - Identificação da Empresa (Header)
 * Este registro é obrigatório e deve ser o primeiro do arquivo para identificar
 * a empresa à qual os dados pertencem.
 *
 * Campos:
 * 1 - Identificação do registro (fixo: 0000)
 * 2 - Inscrição da empresa (CNPJ/CPF/CEI/CAEPF da empresa)
 */
class Registro0000 extends RegistroBase
{
    private string $inscricaoEmpresa;

    public function __construct(string $inscricaoEmpresa)
    {
        $this->inscricaoEmpresa = $inscricaoEmpresa;
    }

    public function getTipoRegistro(): string
    {
        return '0000';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(),           // Campo 1: Identificação do registro
            $this->formatarCampo($this->inscricaoEmpresa, 14, 'C'),  // Campo 2: Inscrição da empresa
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 0000
        return ! empty($this->inscricaoEmpresa) &&
               preg_match('/^\d{11,14}$/', $this->inscricaoEmpresa); // CNPJ ou CPF
    }

    // Getters
    public function getInscricaoEmpresa(): string
    {
        return $this->inscricaoEmpresa;
    }

    // Setters
    public function setInscricaoEmpresa(string $inscricaoEmpresa): void
    {
        $this->inscricaoEmpresa = $inscricaoEmpresa;
    }
}
