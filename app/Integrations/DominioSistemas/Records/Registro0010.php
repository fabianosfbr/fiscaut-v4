<?php

namespace App\Integrations\DominioSistemas\Records;

/**
 * Registro 0010 - Cadastro de Clientes
 * Utilizado para importar ou atualizar o cadastro de clientes.
 *
 * Campos:
 * 1 - Identificação (fixo: 0010)
 * 2 - Inscrição (CNPJ/CPF/CEI/CAEPF)
 * 3 - Razão Social (até 150 caracteres)
 * 4 - Apelido (nome reduzido até 40 caracteres)
 * 5 - Endereço (logradouro)
 * 6 - Número
 * 7 - Complemento
 * 8 - Bairro
 * 9 - Cód. Município (Código IBGE ou RAIS)
 * 10 - UF (XX, EX para exterior)
 * 11 - Código do País (apenas para exterior)
 * 12 - CEP
 * 13 - Inscrição Estadual
 * 14 - Inscrição Municipal
 * 15 - Inscrição Suframa
 * 16 - DDD
 * 17 - Telefone
 * 18 - FAX
 * 19 - Data do Cadastro (dd/mm/aaaa)
 * 20 - Conta Contábil
 * 23 - Natureza Jurídica (1 a 9, 7=Empresa Privada)
 * 24 - Regime Apuração (N, M, E, O, U, I)
 * 25 - Contribuinte ICMS (S/N)
 */
class Registro0010 extends RegistroBase
{
    private string $inscricao;

    private string $razaoSocial;

    private ?string $apelido = null;

    private ?string $endereco = null;

    private ?string $numero = null;

    private ?string $complemento = null;

    private ?string $bairro = null;

    private ?string $codMunicipio = null;

    private ?string $uf = null;

    private ?string $codigoPais = null;

    private ?string $cep = null;

    private ?string $inscricaoEstadual = null;

    private ?string $inscricaoMunicipal = null;

    private ?string $inscricaoSuframa = null;

    private ?string $ddd = null;

    private ?string $telefone = null;

    private ?string $fax = null;

    private ?\DateTime $dataCadastro = null;

    private ?string $contaContabil = null;

    private ?string $naturezaJuridica = null;

    private ?string $regimeApuracao = null;

    private ?string $contribuinteIcms = null;

    public function __construct(
        string $inscricao,
        string $razaoSocial
    ) {
        $this->inscricao = $inscricao;
        $this->razaoSocial = $razaoSocial;
    }

    public function getTipoRegistro(): string
    {
        return '0010';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação
            $this->formatarCampo($this->inscricao, null, 'C'), // Campo 2: Inscrição
            $this->formatarCampo($this->razaoSocial, 150, 'C'), // Campo 3: Razão Social
            $this->formatarCampo($this->apelido, 40, 'C'), // Campo 4: Apelido
            $this->formatarCampo($this->endereco, null, 'C'), // Campo 5: Endereço
            $this->formatarCampo($this->numero, null, 'N'), // Campo 6: Número
            $this->formatarCampo($this->complemento, null, 'C'), // Campo 7: Complemento
            $this->formatarCampo($this->bairro, null, 'C'), // Campo 8: Bairro
            $this->formatarCampo($this->codMunicipio, null, 'N'), // Campo 9: Cód. Município
            $this->formatarCampo($this->uf, 2, 'C'), // Campo 10: UF
            $this->formatarCampo($this->codigoPais, null, 'N'), // Campo 11: Código do País
            $this->formatarCampo($this->cep, null, 'C'), // Campo 12: CEP
            $this->formatarCampo($this->inscricaoEstadual, null, 'C'), // Campo 13: Inscrição Estadual
            $this->formatarCampo($this->inscricaoMunicipal, null, 'C'), // Campo 14: Inscrição Municipal
            $this->formatarCampo($this->inscricaoSuframa, null, 'C'), // Campo 15: Inscrição Suframa
            $this->formatarCampo($this->ddd, null, 'C'), // Campo 16: DDD
            $this->formatarCampo($this->telefone, null, 'C'), // Campo 17: Telefone
            $this->formatarCampo($this->fax, null, 'C'), // Campo 18: FAX
            $this->formatarCampo($this->dataCadastro, null, 'X'), // Campo 19: Data do Cadastro
            $this->formatarCampo($this->contaContabil, null, 'N'), // Campo 20: Conta Contábil
            '', // Campo 21: (vazio)
            '', // Campo 22: (vazio)
            $this->formatarCampo($this->naturezaJuridica, null, 'C'), // Campo 23: Natureza Jurídica
            $this->formatarCampo($this->regimeApuracao, null, 'C'), // Campo 24: Regime Apuração
            $this->formatarCampo($this->contribuinteIcms, null, 'C'), // Campo 25: Contribuinte ICMS
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 0010
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

    public function getApelido(): ?string
    {
        return $this->apelido;
    }

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function getComplemento(): ?string
    {
        return $this->complemento;
    }

    public function getBairro(): ?string
    {
        return $this->bairro;
    }

    public function getCodMunicipio(): ?string
    {
        return $this->codMunicipio;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function getCodigoPais(): ?string
    {
        return $this->codigoPais;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function getInscricaoEstadual(): ?string
    {
        return $this->inscricaoEstadual;
    }

    public function getInscricaoMunicipal(): ?string
    {
        return $this->inscricaoMunicipal;
    }

    public function getInscricaoSuframa(): ?string
    {
        return $this->inscricaoSuframa;
    }

    public function getDdd(): ?string
    {
        return $this->ddd;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function getDataCadastro(): ?\DateTime
    {
        return $this->dataCadastro;
    }

    public function getContaContabil(): ?string
    {
        return $this->contaContabil;
    }

    public function getNaturezaJuridica(): ?string
    {
        return $this->naturezaJuridica;
    }

    public function getRegimeApuracao(): ?string
    {
        return $this->regimeApuracao;
    }

    public function getContribuinteIcms(): ?string
    {
        return $this->contribuinteIcms;
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

    public function setApelido(?string $apelido): void
    {
        $this->apelido = $apelido;
    }

    public function setEndereco(?string $endereco): void
    {
        $this->endereco = $endereco;
    }

    public function setNumero(?string $numero): void
    {
        $this->numero = $numero;
    }

    public function setComplemento(?string $complemento): void
    {
        $this->complemento = $complemento;
    }

    public function setBairro(?string $bairro): void
    {
        $this->bairro = $bairro;
    }

    public function setCodMunicipio(?string $codMunicipio): void
    {
        $this->codMunicipio = $codMunicipio;
    }

    public function setUf(?string $uf): void
    {
        $this->uf = $uf;
    }

    public function setCodigoPais(?string $codigoPais): void
    {
        $this->codigoPais = $codigoPais;
    }

    public function setCep(?string $cep): void
    {
        $this->cep = $cep;
    }

    public function setInscricaoEstadual(?string $inscricaoEstadual): void
    {
        $this->inscricaoEstadual = $inscricaoEstadual;
    }

    public function setInscricaoMunicipal(?string $inscricaoMunicipal): void
    {
        $this->inscricaoMunicipal = $inscricaoMunicipal;
    }

    public function setInscricaoSuframa(?string $inscricaoSuframa): void
    {
        $this->inscricaoSuframa = $inscricaoSuframa;
    }

    public function setDdd(?string $ddd): void
    {
        $this->ddd = $ddd;
    }

    public function setTelefone(?string $telefone): void
    {
        $this->telefone = $telefone;
    }

    public function setFax(?string $fax): void
    {
        $this->fax = $fax;
    }

    public function setDataCadastro(?\DateTime $dataCadastro): void
    {
        $this->dataCadastro = $dataCadastro;
    }

    public function setContaContabil(?string $contaContabil): void
    {
        $this->contaContabil = $contaContabil;
    }

    public function setNaturezaJuridica(?string $naturezaJuridica): void
    {
        $this->naturezaJuridica = $naturezaJuridica;
    }

    public function setRegimeApuracao(?string $regimeApuracao): void
    {
        $this->regimeApuracao = $regimeApuracao;
    }

    public function setContribuinteIcms(?string $contribuinteIcms): void
    {
        $this->contribuinteIcms = $contribuinteIcms;
    }
}
