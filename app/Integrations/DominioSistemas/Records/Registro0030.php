<?php

namespace App\Integrations\DominioSistemas\Records;

/**
 * Registro 0030 - Cadastro de Transportador
 * Utilizado para importar ou atualizar o cadastro de transportadores contidos nas notas fiscais.
 *
 * Campos (baseado no exemplo fornecido):
 * 1 - Identificação do registro (fixo: 0030)
 * 2 - Inscrição CNPJ / CPF do transportador
 * 3 - Razão Social do transportador
 * 4 - Endereço do transportador
 * 5 - Código do município do transportador
 * 6 - UF do transportador
 * 7 - Inscrição Estadual do transportador
 * 8 - Tipo Inscrição
 */
class Registro0030 extends RegistroBase
{
    private string $inscricao;

    private string $razaoSocial;

    private ?string $endereco = null;

    private ?string $codigoMunicipio = null;

    private ?string $uf = null;

    private ?string $inscricaoEstadual = null;

    private ?string $tipoInscricao = null;

    public function __construct(
        $notaFiscal
    ) {
        // Extrai os dados do transportador do XML da nota fiscal usando o método da classe base
        $xmlData = $this->extrairDadosDoXml($notaFiscal, [
            'transportador' => ['transporta', 'transportador', 'Transportador'],
            'endereco_transportador' => ['enderTransp', 'ender_transp', 'EnderecoTransportador'],
        ]);

        // Preenche os campos com base nos dados extraídos do XML
        $transportador = $xmlData['transportador'] ?? [];
        $enderecoTransportador = $xmlData['endereco_transportador'] ?? [];

        $this->inscricao = $transportador['CNPJ'] ?? $transportador['CPF'] ?? $notaFiscal->transportador_cnpj ?? '';
        $this->razaoSocial = $transportador['xNome'] ?? $notaFiscal->transportador_razao_social ?? 'NÃO INFORMADO';
        $this->endereco = $enderecoTransportador['xLgr'] ?? null;
        $this->codigoMunicipio = $enderecoTransportador['cMun'] ?? null;
        $this->uf = $enderecoTransportador['UF'] ?? null;
        $this->inscricaoEstadual = $transportador['IE'] ?? null;
        $this->tipoInscricao = null; // Não há campo específico para tipo de inscrição no transporte
    }

    public function getTipoRegistro(): string
    {
        return '0030';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação do registro (0030)
            $this->formatarCampo($this->inscricao, null, 'C'), // Campo 2: Inscrição (CNPJ/CPF do transportador)
            $this->formatarCampo($this->razaoSocial, null, 'C'), // Campo 3: Razão Social do transportador
            $this->formatarCampo($this->endereco, null, 'C'), // Campo 4: Endereço do transportador
            $this->formatarCampo($this->codigoMunicipio, null, 'N'), // Campo 5: Código do município do transportador
            $this->formatarCampo($this->uf, 2, 'C'), // Campo 6: UF do transportador
            $this->formatarCampo($this->inscricaoEstadual, null, 'C'), // Campo 7: Inscrição Estadual do transportador
            $this->formatarCampo($this->tipoInscricao, null, 'C'), // Campo 8: Tipo Inscrição
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 0030
        return ! empty($this->inscricao) && ! empty($this->razaoSocial);
    }

    // Getters
    public function getInscricao(): string
    {
        return $this->inscricao;
    }

    public function getRazaoSocial(): string
    {
        return $this->razaoSocial;
    }

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function getCodigoMunicipio(): ?string
    {
        return $this->codigoMunicipio;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function getInscricaoEstadual(): ?string
    {
        return $this->inscricaoEstadual;
    }

    public function getTipoInscricao(): ?string
    {
        return $this->tipoInscricao;
    }

    // Setters
    public function setInscricao(string $inscricao): void
    {
        $this->inscricao = $inscricao;
    }

    public function setRazaoSocial(string $razaoSocial): void
    {
        $this->razaoSocial = $razaoSocial;
    }

    public function setEndereco(?string $endereco): void
    {
        $this->endereco = $endereco;
    }

    public function setCodigoMunicipio(?string $codigoMunicipio): void
    {
        $this->codigoMunicipio = $codigoMunicipio;
    }

    public function setUf(?string $uf): void
    {
        $this->uf = $uf;
    }

    public function setInscricaoEstadual(?string $inscricaoEstadual): void
    {
        $this->inscricaoEstadual = $inscricaoEstadual;
    }

    public function setTipoInscricao(?string $tipoInscricao): void
    {
        $this->tipoInscricao = $tipoInscricao;
    }
}
