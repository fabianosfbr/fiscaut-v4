<?php

namespace App\Integrations\DominioSistemas\Records;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\NotaFiscalEletronica;
use App\Services\Xml\XmlReaderService;

/**
 * Registro 0020 - Cadastro de Fornecedores
 * Utilizado para importar ou atualizar o cadastro de fornecedores.
 *
 * Campos:
 * 1 - Identificação (fixo: 0020)
 * 2 - Inscrição (CNPJ/CPF/CEI/CAEPF do fornecedor, apenas números)
 * 3 - Razão Social (máximo de 150 caracteres)
 * 4 - Apelido (número reduzido, máximo de 40 caracteres)
 * 5 - Endereço
 * 6 - Número
 * 7 - Complemento
 * 8 - Bairro
 * 9 - Cód. Município (Código do município: estadual, federal ou IBGE/RAIS)
 * 10 - UF (EX para exterior)
 * 11 - Código do País (informar apenas quando for exterior)
 * 12 - CEP
 * 13 - Inscrição Estadual
 * 14 - Inscrição Municipal
 * 15 - Inscrição Suframa
 * 16 - DDD
 * 17 - Telefone
 * 18 - FAX
 * 19 - Data do Cadastro (dd/mm/aaaa)
 * 20 - Conta Contábil
 * 21 - Conta Contábil Cliente
 * 22 - Agropecuário (S/N)
 * 23 - Natureza Jurídica (1 a 8)
 * 24 - Regime de Apuração (N, M, E, O, U, I)
 * 25 - Contribuinte ICMS (S/N)
 * 26 - Alíquota ICMS
 * 27 - Categoria do Estabelecimento
 * 28 - Inscrição Estadual ST
 * 29 - Email
 * 30 - Interdependência (S/N)
 * 31 - Contribuinte da CPRB (S/N)
 * 32 - Processo adm./judicial (limite de 21 caracteres)
 * 33 - Tipo Inscrição (1)
 */
class Registro0020 extends RegistroBase
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
    private ?string $contaContabilCliente = null;
    private ?string $agropecuario = null; // S/N
    private ?string $naturezaJuridica = null; // 1 a 8
    private ?string $regimeApuracao = null; // N, M, E, O, U, I
    private ?string $contribuinteIcms = null; // S/N
    private ?float $aliquotaIcms = null;
    private ?string $categoriaEstabelecimento = null;
    private ?string $inscricaoEstadualSt = null;
    private ?string $email = null;
    private ?string $interdependencia = null; // S/N
    private ?string $contribuinteCprb = null; // S/N
    private ?string $processoAdministrativoJudicial = null;
    private ?string $tipoInscricao = null; // 1

    public function __construct(
        $notaFiscal
    ) {
        // Extrai os dados do XML da nota fiscal usando o método da classe base
        $xmlData = $this->extrairDadosDoXml($notaFiscal, [
            'emitente' => ['emit', 'emitente', 'Emitente'],
            'endereco_emitente' => ['enderEmit', 'ender_emit', 'EnderecoEmitente']
        ]);

        // Preenche os campos com base nos dados extraídos do XML
        $emitente = $xmlData['emitente'] ?? [];
        $enderecoEmitente = $xmlData['endereco_emitente'] ?? [];

        $this->inscricao = $emitente['CNPJ'] ?? $emitente['CPF'] ?? $notaFiscal->emitente_cnpj ?? '';
        $this->razaoSocial = $emitente['xNome'] ?? $emitente['xFant'] ?? $notaFiscal->emitente_razao_social ?? 'NÃO INFORMADO';
        $this->endereco = $enderecoEmitente['xLgr'] ?? null;
        $this->numero = $enderecoEmitente['nro'] ?? null;
        $this->complemento = $enderecoEmitente['xCpl'] ?? null;
        $this->bairro = $enderecoEmitente['xBairro'] ?? null;
        $this->codMunicipio = $enderecoEmitente['cMun'] ?? null;
        $this->uf = $enderecoEmitente['UF'] ?? null;
        $this->cep = $enderecoEmitente['CEP'] ?? null;
        $this->inscricaoEstadual = $emitente['IE'] ?? null;
        $this->inscricaoMunicipal = $emitente['IM'] ?? null;
    }

    public function getTipoRegistro(): string
    {
        return '0020';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação do registro
            $this->formatarCampo($this->inscricao, null, 'C'), // Campo 2: Inscrição (CNPJ/CPF/CEI/CAEPF do cliente)
            $this->formatarCampo($this->razaoSocial, 150, 'C'), // Campo 3: Razão Social (Máximo de 150 caracteres)
            $this->formatarCampo($this->apelido, 40, 'C'), // Campo 4: Apelido (Número reduzido, máximo de 40 caracteres)
            $this->formatarCampo($this->endereco, null, 'C'), // Campo 5: Endereço
            $this->formatarCampo($this->numero, null, 'N'), // Campo 6: Número
            $this->formatarCampo($this->complemento, null, 'C'), // Campo 7: Complemento
            $this->formatarCampo($this->bairro, null, 'C'), // Campo 8: Bairro
            $this->formatarCampo($this->codMunicipio, null, 'N'), // Campo 9: Cód. Município (Código do município: estadual, federal ou IBGE/RAIS)
            $this->formatarCampo($this->uf, 2, 'C'), // Campo 10: UF (Quando for exterior, informar EX)
            $this->formatarCampo($this->codigoPais, null, 'N'), // Campo 11: Código do País (Informar apenas quando for exterior, o código de cadastro do país)
            $this->formatarCampo($this->cep, null, 'C'), // Campo 12: CEP
            $this->formatarCampo($this->inscricaoEstadual, null, 'C'), // Campo 13: Inscrição Estadual
            $this->formatarCampo($this->inscricaoMunicipal, null, 'C'), // Campo 14: Inscrição Municipal
            $this->formatarCampo($this->inscricaoSuframa, null, 'C'), // Campo 15: Inscrição Suframa
            $this->formatarCampo($this->ddd, null, 'C'), // Campo 16: DDD
            $this->formatarCampo($this->telefone, null, 'C'), // Campo 17: Telefone
            $this->formatarCampo($this->fax, null, 'C'), // Campo 18: FAX
            $this->formatarCampo($this->dataCadastro, null, 'X'), // Campo 19: Data do Cadastro (dd/mm/aaaa)
            $this->formatarCampo($this->contaContabil, null, 'N'), // Campo 20: Conta Contábil
            $this->formatarCampo($this->contaContabilCliente, null, 'N'), // Campo 21: Conta Contábil Cliente (Informar a conta contábil como fornecedor, para quando ocorrer devolução de vendas)
            $this->formatarCampo($this->agropecuario, null, 'C'), // Campo 22: Agropecuário (Informar S=Sim ou N=Não)
            $this->formatarCampo($this->naturezaJuridica, null, 'C'), // Campo 23: Natureza Jurídica (1=Órgão Público Federal, 2=Órgão Público Estadual, 3=Órgão Público Municipal, 4=Empresa Pública Federal, 5=Empresa Pública Estadual, 6=Empresa Pública Municipal, 7=Empresa Privada ou 8=Sociedade Cooperativa)
            $this->formatarCampo($this->regimeApuracao, null, 'C'), // Campo 24: Regime de Apuração (N=Normal, M=Microempresa, E=Empresa de pequeno porte, O=Outros, U=Imune do IRPJ ou I=Isenta do IRPJ)
            $this->formatarCampo($this->contribuinteIcms, null, 'C'), // Campo 25: Contribuinte ICMS (Informar S=Sim ou N=Não)
            $this->formatarCampo($this->aliquotaIcms, null, 'D'), // Campo 26: Alíquota ICMS (Quando contribuinte do ICMS=Sim, informar a alíquota de ICMS aplicável ao cliente)
            $this->formatarCampo($this->categoriaEstabelecimento, null, 'C'), // Campo 27: Categoria do Estabelecimento (Informar apenas se a empresa gera o informativo SCANC-CTB. ARM, CNF, CPQ, DIS, FOR, IMP, PRV, REF, TRR, USI ou VGL)
            $this->formatarCampo($this->inscricaoEstadualSt, null, 'C'), // Campo 28: Inscrição Estadual ST
            $this->formatarCampo($this->email, null, 'C'), // Campo 29: Email
            $this->formatarCampo($this->interdependencia, null, 'C'), // Campo 30: Interdependência (Informar S=Sim ou N=Não)
            $this->formatarCampo($this->contribuinteCprb, null, 'C'), // Campo 31: Contribuinte da CPRB (Informar S=Sim ou N=Não)
            $this->formatarCampo($this->processoAdministrativoJudicial, 21, 'C'), // Campo 32: Processo adm./judicial (Limite de 21 caracteres)
            $this->formatarCampo($this->tipoInscricao, null, 'C'), // Campo 33: Tipo Inscrição (Informar: 1=CAEPF)
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 0020
        return !empty($this->inscricao) && !empty($this->razaoSocial);
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
    public function getContaContabilCliente(): ?string
    {
        return $this->contaContabilCliente;
    }
    public function getAgropecuario(): ?string
    {
        return $this->agropecuario;
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
    public function getAliquotaIcms(): ?float
    {
        return $this->aliquotaIcms;
    }
    public function getCategoriaEstabelecimento(): ?string
    {
        return $this->categoriaEstabelecimento;
    }
    public function getInscricaoEstadualSt(): ?string
    {
        return $this->inscricaoEstadualSt;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function getInterdependencia(): ?string
    {
        return $this->interdependencia;
    }
    public function getContribuinteCprb(): ?string
    {
        return $this->contribuinteCprb;
    }
    public function getProcessoAdministrativoJudicial(): ?string
    {
        return $this->processoAdministrativoJudicial;
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
    public function setContaContabilCliente(?string $contaContabilCliente): void
    {
        $this->contaContabilCliente = $contaContabilCliente;
    }
    public function setAgropecuario(?string $agropecuario): void
    {
        $this->agropecuario = $agropecuario;
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
    public function setAliquotaIcms(?float $aliquotaIcms): void
    {
        $this->aliquotaIcms = $aliquotaIcms;
    }
    public function setCategoriaEstabelecimento(?string $categoriaEstabelecimento): void
    {
        $this->categoriaEstabelecimento = $categoriaEstabelecimento;
    }
    public function setInscricaoEstadualSt(?string $inscricaoEstadualSt): void
    {
        $this->inscricaoEstadualSt = $inscricaoEstadualSt;
    }
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
    public function setInterdependencia(?string $interdependencia): void
    {
        $this->interdependencia = $interdependencia;
    }
    public function setContribuinteCprb(?string $contribuinteCprb): void
    {
        $this->contribuinteCprb = $contribuinteCprb;
    }
    public function setProcessoAdministrativoJudicial(?string $processoAdministrativoJudicial): void
    {
        $this->processoAdministrativoJudicial = $processoAdministrativoJudicial;
    }
    public function setTipoInscricao(?string $tipoInscricao): void
    {
        $this->tipoInscricao = $tipoInscricao;
    }
}
