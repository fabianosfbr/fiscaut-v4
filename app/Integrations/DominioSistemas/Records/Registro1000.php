<?php

namespace App\Integrations\DominioSistemas\Records;

/**
 * Registro 1000 - Notas Fiscais de Entrada
 * Notas Fiscais de Entrada.
 * 
 * Campos:
 * 1 - Identificação do registro (fixo: 1000)
 * 2 - Código da espécie
 * 3 - Inscrição fornecedor
 * 4 - Código de Exclusão da DIEF
 * 5 - Código do acumulador
 * 6 - CFOP
 * 7 - Segmento
 * 8 - Número do documento
 * 9 - Série
 * 10 - Numero do documento final
 * 11 - Data da entrada
 * 12 - Data emissão
 * 13 - Valor contábil
 * 14 - Valor da exclusão da DIEF
 * 15 - Observação
 * 16 - Modalidade do frete (C, F, S, T, R, D)
 * 17 - Emitente da nota fiscal (P, T)
 * 18 - CFOP estendido/detalhamento
 * 19 - Código da transferência de crédito
 * 20 - Código do Recolhimento do ISS Retido
 * 21 - Código do Recolhimento do IRRF
 * 22 - Código da observação
 * 23 - Data do visto notas de transf. Crédito ICMS
 * 24 - Fato gerador da CRF (E, P)
 * 25 - Fato gerador do IRRF (E, P)
 * 26 - Valor do frete
 * 27 - Valor do seguro
 * 28 - Valor da despesas
 * 29 - Valor do PIS
 * 30 - Código que Identifica o tipo de Antecipação Tributária
 * 31 - Valor do COFINS
 * 32 - Valor calculado referente a DARE da nota
 * 33 - Alíquota do valor calculado referente a DARE da nota
 * 34 - Valor da base de cálculo do ICMS ST
 * 35 - Entradas cuja saídas é isenta
 * 36 - Outras entradas isentas
 * 37 - Valor transporte incluído na base
 * 38 - Código de ressarcimento
 * 39 - Valor produtos
 * 40 - Município Origem
 * 41 - Situação da Nota
 * 42 - Código da situação tributária
 * 43 - Sub serie
 * 44 - Inscrição estadual do fornecedor
 * 45 - Inscrição municipal do fornecedor
 * 46 - Código da operação e prestação
 * 47 - Valor a ser deduzido da receita tributável
 * 48 - Competência
 * 49 - Operação
 * 50 - Número do parecer fiscal
 * 51 - Data do parecer fiscal
 * 52 - Número da declaração de Importação
 * 53 - Possui benefício fiscal (S, N)
 * 54 - Chave da nota fiscal eletrônica
 * 55 - Código de recolhimento do FETHAB
 * 56 - Responsável pelo recolhimento do FETHAB (E, C)
 * 57 - CFOP documento fiscal
 * 58 - Tipo de CT-e
 * 59 - CT-e referência
 * 60 - Modalidade da importação
 * 61 - Código da informação complementar
 * 62 - Informação complementar
 * 63 - Classe de consumo
 * 64 - Tipo de ligação
 * 65 - Grupo de tensão
 * 66 - Tipo de assinante
 * 67 - KWH consumido
 * 68 - Valor fornecido / consumido de gás ou energia elétrica
 * 69 - Valor cobrado de terceiros
 * 70 - Tipo do documento de importação
 * 71 - Número do Ato Concessório do regime Drawback
 * 72 - Natureza do frete PIS/COFINS
 * 73 - CST – PIS/COFINS
 * 74 - Base do crédito PIS/COFINS
 * 75 - Valor serviços / itens PIS/COFINS
 * 76 - Base de cálculo PIS/COFINS
 * 77 - Alíquota de PIS
 * 78 - Alíquota de COFINS
 * 79 - Chave de NFSe
 * 80 - Número do processo ou ato concessório
 * 81 - Origem do processo
 * 82 - Data da escrituração
 * 83 - CFPS
 * 84 - Natureza da receita – PIS/COFINS
 * 85 - CST IPI – Código da Situação Tributária do IPI
 * 86 - Lançamentos de SCP
 * 87 - Tipo de serviço
 * 88 - Município destino
 * 89 - Pedágio
 * 90 - IPI
 * 91 - ICMS ST
 * 92 - Classificação de Serviços Prestados mediante cessão de mão de obra/Empreitada - Tipo de serviço - EFD-Reinf
 */
class Registro1000 extends RegistroBase
{
    private string $codigoEspecie;
    private string $inscricaoFornecedor;
    private ?string $codigoExclusaoDief = null;
    private ?string $codigoAcumulador = null;
    private string $cfop;
    private ?string $segmento = null;
    private int $numeroDocumento;
    private ?string $serie = null;
    private ?int $numeroDocumentoFinal = null;
    private \DateTime $dataEntrada;
    private \DateTime $dataEmissao;
    private float $valorContabil;
    private ?float $valorExclusaoDief = null;
    private ?string $observacao = null;
    private ?string $modalidadeFrete = null; // C, F, S, T, R, D
    private ?string $emitenteNotaFiscal = null; // P, T
    private ?string $cfopExtendidoDetalhamento = null;
    private ?string $codigoTransferenciaCredito = null;
    private ?string $codigoRecolhimentoIssRetido = null;
    private ?string $codigoRecolhimentoIrrf = null;
    private ?string $codigoObservacao = null;
    private ?\DateTime $dataVistoTransfCreditoIcms = null;
    private ?string $fatoGeradorCrf = null; // E, P
    private ?string $fatoGeradorIrrf = null; // E, P
    private ?float $valorFrete = null;
    private ?float $valorSeguro = null;
    private ?float $valorDespesas = null;
    private ?float $valorPis = null;
    private ?string $codigoAntecipacaoTributaria = null;
    private ?float $valorCofins = null;
    private ?float $valorDareNota = null;
    private ?float $aliquotaDareNota = null;
    private ?float $valorBaseCalculoIcmsSt = null;
    private ?float $entradasSaidasIsentas = null;
    private ?float $outrasEntradasIsentas = null;
    private ?float $valorTransporteIncluidoBase = null;
    private ?string $codigoRessarcimento = null;
    private float $valorProdutos;
    private string $municipioOrigem;
    private int $situacaoNota;
    private ?string $codigoSituacaoTributaria = null;
    private ?string $subSerie = null;
    private ?string $inscricaoEstadualFornecedor = null;
    private ?string $inscricaoMunicipalFornecedor = null;
    private ?string $codigoOperacaoPrestacao = null;
    private ?float $valorDeduzirReceitaTributavel = null;
    private ?\DateTime $competencia = null;
    private ?int $operacao = null;
    private ?string $numeroParecerFiscal = null;
    private ?\DateTime $dataParecerFiscal = null;
    private ?string $numeroDeclaracaoImportacao = null;
    private ?string $possuiBeneficioFiscal = null; // S, N
    private ?string $chaveNotaFiscalEletronica = null;
    private ?string $codigoRecolhimentoFethab = null;
    private ?string $responsavelRecolhimentoFethab = null; // E, C
    private ?string $cfopDocumentoFiscal = null;
    private ?int $tipoCte = null;
    private ?string $cteReferencia = null;
    private ?int $modalidadeImportacao = null;
    private ?string $codigoInformacaoComplementar = null;
    private ?string $informacaoComplementar = null;
    private ?int $classeConsumo = null;
    private ?int $tipoLigacao = null;
    private ?int $grupoTensao = null;
    private ?int $tipoAssinante = null;
    private ?int $kwhConsumido = null;
    private ?float $valorFornecidoConsumidoGasEnergia = null;
    private ?float $valorCobradoTerceiros = null;
    private ?int $tipoDocumentoImportacao = null;
    private ?string $numeroAtoConcessorioDrawback = null;
    private ?int $naturezaFretePisCofins = null;
    private ?int $cstPisCofins = null;
    private ?int $baseCreditoPisCofins = null;
    private ?float $valorServicosItensPisCofins = null;
    private ?float $baseCalculoPisCofins = null;
    private ?float $aliquotaPis = null;
    private ?float $aliquotaCofins = null;
    private ?string $chaveNfse = null;
    private ?string $numeroProcessoAtoConcessorio = null;
    private ?string $origemProcesso = null;
    private ?\DateTime $dataEscrituracao = null;
    private ?int $cfps = null;
    private ?int $naturezaReceitaPisCofins = null;
    private ?string $cstIpi = null;
    private ?int $lancamentosScp = null;
    private ?int $tipoServico = null;
    private ?int $municipioDestino = null;
    private ?float $pedagio = null;
    private ?float $ipi = null;
    private ?float $icmsSt = null;
    private ?int $classificacaoServicos = null;

    public function __construct(
        string $codigoEspecie,
        string $inscricaoFornecedor,
        string $cfop,
        int $numeroDocumento,
        \DateTime $dataEntrada,
        \DateTime $dataEmissao,
        float $valorContabil,
        float $valorProdutos,
        string $municipioOrigem,
        int $situacaoNota
    ) {
        $this->codigoEspecie = $codigoEspecie;
        $this->inscricaoFornecedor = $inscricaoFornecedor;
        $this->cfop = $cfop;
        $this->numeroDocumento = $numeroDocumento;
        $this->dataEntrada = $dataEntrada;
        $this->dataEmissao = $dataEmissao;
        $this->valorContabil = $valorContabil;
        $this->valorProdutos = $valorProdutos;
        $this->municipioOrigem = $municipioOrigem;
        $this->situacaoNota = $situacaoNota;
    }

    public function getTipoRegistro(): string
    {
        return '1000';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação do registro
            $this->formatarCampo($this->codigoEspecie, null, 'N'), // Campo 2: Código da espécie
            $this->formatarCampo($this->inscricaoFornecedor, null, 'C'), // Campo 3: Inscrição fornecedor
            $this->formatarCampo($this->codigoExclusaoDief, null, 'N'), // Campo 4: Código de Exclusão da DIEF
            $this->formatarCampo($this->codigoAcumulador, null, 'N'), // Campo 5: Código do acumulador
            $this->formatarCampo($this->cfop, null, 'N'), // Campo 6: CFOP
            $this->formatarCampo($this->segmento, null, 'N'), // Campo 7: Segmento
            $this->formatarCampo($this->numeroDocumento, null, 'N'), // Campo 8: Número do documento
            $this->formatarCampo($this->serie, null, 'C'), // Campo 9: Série
            $this->formatarCampo($this->numeroDocumentoFinal, null, 'N'), // Campo 10: Numero do documento final
            $this->formatarCampo($this->dataEntrada, null, 'X'), // Campo 11: Data da entrada
            $this->formatarCampo($this->dataEmissao, null, 'X'), // Campo 12: Data emissão
            $this->formatarCampo($this->valorContabil, null, 'D'), // Campo 13: Valor contábil
            $this->formatarCampo($this->valorExclusaoDief, null, 'D'), // Campo 14: Valor da exclusão da DIEF
            $this->formatarCampo($this->observacao, null, 'C'), // Campo 15: Observação
            $this->formatarCampo($this->modalidadeFrete, null, 'C'), // Campo 16: Modalidade do frete
            $this->formatarCampo($this->emitenteNotaFiscal, null, 'C'), // Campo 17: Emitente da nota fiscal
            $this->formatarCampo($this->cfopExtendidoDetalhamento, null, 'N'), // Campo 18: CFOP estendido/detalhamento
            $this->formatarCampo($this->codigoTransferenciaCredito, null, 'N'), // Campo 19: Código da transferência de crédito
            $this->formatarCampo($this->codigoRecolhimentoIssRetido, null, 'C'), // Campo 20: Código do Recolhimento do ISS Retido
            $this->formatarCampo($this->codigoRecolhimentoIrrf, null, 'C'), // Campo 21: Código do Recolhimento do IRRF
            $this->formatarCampo($this->codigoObservacao, null, 'N'), // Campo 22: Código da observação
            $this->formatarCampo($this->dataVistoTransfCreditoIcms, null, 'X'), // Campo 23: Data do visto notas de transf. Crédito ICMS
            $this->formatarCampo($this->fatoGeradorCrf, null, 'C'), // Campo 24: Fato gerador da CRF
            $this->formatarCampo($this->fatoGeradorIrrf, null, 'C'), // Campo 25: Fato gerador do IRRF
            $this->formatarCampo($this->valorFrete, null, 'D'), // Campo 26: Valor do frete
            $this->formatarCampo($this->valorSeguro, null, 'D'), // Campo 27: Valor do seguro
            $this->formatarCampo($this->valorDespesas, null, 'D'), // Campo 28: Valor da despesas
            $this->formatarCampo($this->valorPis, null, 'D'), // Campo 29: Valor do PIS
            $this->formatarCampo($this->codigoAntecipacaoTributaria, null, 'N'), // Campo 30: Código que Identifica o tipo de Antecipação Tributária
            $this->formatarCampo($this->valorCofins, null, 'D'), // Campo 31: Valor do COFINS
            $this->formatarCampo($this->valorDareNota, null, 'D'), // Campo 32: Valor calculado referente a DARE da nota
            $this->formatarCampo($this->aliquotaDareNota, null, 'D'), // Campo 33: Alíquota do valor calculado referente a DARE da nota
            $this->formatarCampo($this->valorBaseCalculoIcmsSt, null, 'N'), // Campo 34: Valor da base de cálculo do ICMS ST
            $this->formatarCampo($this->entradasSaidasIsentas, null, 'D'), // Campo 35: Entradas cuja saídas é isenta
            $this->formatarCampo($this->outrasEntradasIsentas, null, 'D'), // Campo 36: Outras entradas isentas
            $this->formatarCampo($this->valorTransporteIncluidoBase, null, 'D'), // Campo 37: Valor transporte incluído na base
            $this->formatarCampo($this->codigoRessarcimento, null, 'N'), // Campo 38: Código de ressarcimento
            $this->formatarCampo($this->valorProdutos, null, 'D'), // Campo 39: Valor produtos
            $this->formatarCampo($this->municipioOrigem, null, 'N'), // Campo 40: Município Origem
            $this->formatarCampo($this->situacaoNota, null, 'N'), // Campo 41: Situação da Nota
            $this->formatarCampo($this->codigoSituacaoTributaria, null, 'N'), // Campo 42: Código da situação tributária
            $this->formatarCampo($this->subSerie, null, 'N'), // Campo 43: Sub serie
            $this->formatarCampo($this->inscricaoEstadualFornecedor, null, 'C'), // Campo 44: Inscrição estadual do fornecedor
            $this->formatarCampo($this->inscricaoMunicipalFornecedor, null, 'C'), // Campo 45: Inscrição municipal do fornecedor
            $this->formatarCampo($this->codigoOperacaoPrestacao, null, 'C'), // Campo 46: Código da operação e prestação
            $this->formatarCampo($this->valorDeduzirReceitaTributavel, null, 'D'), // Campo 47: Valor a ser deduzido da receita tributável
            $this->formatarCampo($this->competencia, null, 'X'), // Campo 48: Competência
            $this->formatarCampo($this->operacao, null, 'N'), // Campo 49: Operação
            $this->formatarCampo($this->numeroParecerFiscal, null, 'C'), // Campo 50: Número do parecer fiscal
            $this->formatarCampo($this->dataParecerFiscal, null, 'X'), // Campo 51: Data do parecer fiscal
            $this->formatarCampo($this->numeroDeclaracaoImportacao, null, 'C'), // Campo 52: Número da declaração de Importação
            $this->formatarCampo($this->possuiBeneficioFiscal, null, 'C'), // Campo 53: Possui benefício fiscal
            $this->formatarCampo($this->chaveNotaFiscalEletronica, null, 'C'), // Campo 54: Chave da nota fiscal eletrônica
            $this->formatarCampo($this->codigoRecolhimentoFethab, null, 'C'), // Campo 55: Código de recolhimento do FETHAB
            $this->formatarCampo($this->responsavelRecolhimentoFethab, null, 'C'), // Campo 56: Responsável pelo recolhimento do FETHAB
            $this->formatarCampo($this->cfopDocumentoFiscal, null, 'N'), // Campo 57: CFOP documento fiscal
            $this->formatarCampo($this->tipoCte, null, 'N'), // Campo 58: Tipo de CT-e
            $this->formatarCampo($this->cteReferencia, null, 'C'), // Campo 59: CT-e referência
            $this->formatarCampo($this->modalidadeImportacao, null, 'N'), // Campo 60: Modalidade da importação
            $this->formatarCampo($this->codigoInformacaoComplementar, null, 'N'), // Campo 61: Código da informação complementar
            $this->formatarCampo($this->informacaoComplementar, null, 'C'), // Campo 62: Informação complementar
            $this->formatarCampo($this->classeConsumo, null, 'N'), // Campo 63: Classe de consumo
            $this->formatarCampo($this->tipoLigacao, null, 'N'), // Campo 64: Tipo de ligação
            $this->formatarCampo($this->grupoTensao, null, 'N'), // Campo 65: Grupo de tensão
            $this->formatarCampo($this->tipoAssinante, null, 'N'), // Campo 66: Tipo de assinante
            $this->formatarCampo($this->kwhConsumido, null, 'N'), // Campo 67: KWH consumido
            $this->formatarCampo($this->valorFornecidoConsumidoGasEnergia, null, 'D'), // Campo 68: Valor fornecido / consumido de gás ou energia elétrica
            $this->formatarCampo($this->valorCobradoTerceiros, null, 'D'), // Campo 69: Valor cobrado de terceiros
            $this->formatarCampo($this->tipoDocumentoImportacao, null, 'N'), // Campo 70: Tipo do documento de importação
            $this->formatarCampo($this->numeroAtoConcessorioDrawback, null, 'C'), // Campo 71: Número do Ato Concessório do regime Drawback
            $this->formatarCampo($this->naturezaFretePisCofins, null, 'N'), // Campo 72: Natureza do frete PIS/COFINS
            $this->formatarCampo($this->cstPisCofins, null, 'N'), // Campo 73: CST – PIS/COFINS
            $this->formatarCampo($this->baseCreditoPisCofins, null, 'N'), // Campo 74: Base do crédito PIS/COFINS
            $this->formatarCampo($this->valorServicosItensPisCofins, null, 'D'), // Campo 75: Valor serviços / itens PIS/COFINS
            $this->formatarCampo($this->baseCalculoPisCofins, null, 'D'), // Campo 76: Base de cálculo PIS/COFINS
            $this->formatarCampo($this->aliquotaPis, null, 'D'), // Campo 77: Alíquota de PIS
            $this->formatarCampo($this->aliquotaCofins, null, 'D'), // Campo 78: Alíquota de COFINS
            $this->formatarCampo($this->chaveNfse, null, 'C'), // Campo 79: Chave de NFSe
            $this->formatarCampo($this->numeroProcessoAtoConcessorio, null, 'C'), // Campo 80: Número do processo ou ato concessório
            $this->formatarCampo($this->origemProcesso, null, 'C'), // Campo 81: Origem do processo
            $this->formatarCampo($this->dataEscrituracao, null, 'X'), // Campo 82: Data da escrituração
            $this->formatarCampo($this->cfps, null, 'N'), // Campo 83: CFPS
            $this->formatarCampo($this->naturezaReceitaPisCofins, null, 'N'), // Campo 84: Natureza da receita – PIS/COFINS
            $this->formatarCampo($this->cstIpi, null, 'C'), // Campo 85: CST IPI – Código da Situação Tributária do IPI
            $this->formatarCampo($this->lancamentosScp, null, 'N'), // Campo 86: Lançamentos de SCP
            $this->formatarCampo($this->tipoServico, null, 'N'), // Campo 87: Tipo de serviço
            $this->formatarCampo($this->municipioDestino, null, 'N'), // Campo 88: Município destino
            $this->formatarCampo($this->pedagio, null, 'D'), // Campo 89: Pedágio
            $this->formatarCampo($this->ipi, null, 'D'), // Campo 90: IPI
            $this->formatarCampo($this->icmsSt, null, 'D'), // Campo 91: ICMS ST
            $this->formatarCampo($this->classificacaoServicos, null, 'N'), // Campo 92: Classificação de Serviços
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 1000
        return !empty($this->codigoEspecie) && 
               !empty($this->inscricaoFornecedor) && 
               !empty($this->cfop) && 
               $this->numeroDocumento > 0 &&
               $this->valorContabil >= 0 &&
               $this->valorProdutos >= 0;
    }

    // Getters
    public function getCodigoEspecie(): string { return $this->codigoEspecie; }
    public function getInscricaoFornecedor(): string { return $this->inscricaoFornecedor; }
    public function getCodigoExclusaoDief(): ?string { return $this->codigoExclusaoDief; }
    public function getCodigoAcumulador(): ?string { return $this->codigoAcumulador; }
    public function getCfop(): string { return $this->cfop; }
    public function getSegmento(): ?string { return $this->segmento; }
    public function getNumeroDocumento(): int { return $this->numeroDocumento; }
    public function getSerie(): ?string { return $this->serie; }
    public function getNumeroDocumentoFinal(): ?int { return $this->numeroDocumentoFinal; }
    public function getDataEntrada(): \DateTime { return $this->dataEntrada; }
    public function getDataEmissao(): \DateTime { return $this->dataEmissao; }
    public function getValorContabil(): float { return $this->valorContabil; }
    public function getValorExclusaoDief(): ?float { return $this->valorExclusaoDief; }
    public function getObservacao(): ?string { return $this->observacao; }
    public function getModalidadeFrete(): ?string { return $this->modalidadeFrete; }
    public function getEmitenteNotaFiscal(): ?string { return $this->emitenteNotaFiscal; }
    public function getCfopExtendidoDetalhamento(): ?string { return $this->cfopExtendidoDetalhamento; }
    public function getCodigoTransferenciaCredito(): ?string { return $this->codigoTransferenciaCredito; }
    public function getCodigoRecolhimentoIssRetido(): ?string { return $this->codigoRecolhimentoIssRetido; }
    public function getCodigoRecolhimentoIrrf(): ?string { return $this->codigoRecolhimentoIrrf; }
    public function getCodigoObservacao(): ?string { return $this->codigoObservacao; }
    public function getDataVistoTransfCreditoIcms(): ?\DateTime { return $this->dataVistoTransfCreditoIcms; }
    public function getFatoGeradorCrf(): ?string { return $this->fatoGeradorCrf; }
    public function getFatoGeradorIrrf(): ?string { return $this->fatoGeradorIrrf; }
    public function getValorFrete(): ?float { return $this->valorFrete; }
    public function getValorSeguro(): ?float { return $this->valorSeguro; }
    public function getValorDespesas(): ?float { return $this->valorDespesas; }
    public function getValorPis(): ?float { return $this->valorPis; }
    public function getCodigoAntecipacaoTributaria(): ?string { return $this->codigoAntecipacaoTributaria; }
    public function getValorCofins(): ?float { return $this->valorCofins; }
    public function getValorDareNota(): ?float { return $this->valorDareNota; }
    public function getAliquotaDareNota(): ?float { return $this->aliquotaDareNota; }
    public function getValorBaseCalculoIcmsSt(): ?float { return $this->valorBaseCalculoIcmsSt; }
    public function getEntradasSaidasIsentas(): ?float { return $this->entradasSaidasIsentas; }
    public function getOutrasEntradasIsentas(): ?float { return $this->outrasEntradasIsentas; }
    public function getValorTransporteIncluidoBase(): ?float { return $this->valorTransporteIncluidoBase; }
    public function getCodigoRessarcimento(): ?string { return $this->codigoRessarcimento; }
    public function getValorProdutos(): float { return $this->valorProdutos; }
    public function getMunicipioOrigem(): string { return $this->municipioOrigem; }
    public function getSituacaoNota(): int { return $this->situacaoNota; }
    public function getCodigoSituacaoTributaria(): ?string { return $this->codigoSituacaoTributaria; }
    public function getSubSerie(): ?string { return $this->subSerie; }
    public function getInscricaoEstadualFornecedor(): ?string { return $this->inscricaoEstadualFornecedor; }
    public function getInscricaoMunicipalFornecedor(): ?string { return $this->inscricaoMunicipalFornecedor; }
    public function getCodigoOperacaoPrestacao(): ?string { return $this->codigoOperacaoPrestacao; }
    public function getValorDeduzirReceitaTributavel(): ?float { return $this->valorDeduzirReceitaTributavel; }
    public function getCompetencia(): ?\DateTime { return $this->competencia; }
    public function getOperacao(): ?int { return $this->operacao; }
    public function getNumeroParecerFiscal(): ?string { return $this->numeroParecerFiscal; }
    public function getDataParecerFiscal(): ?\DateTime { return $this->dataParecerFiscal; }
    public function getNumeroDeclaracaoImportacao(): ?string { return $this->numeroDeclaracaoImportacao; }
    public function getPossuiBeneficioFiscal(): ?string { return $this->possuiBeneficioFiscal; }
    public function getChaveNotaFiscalEletronica(): ?string { return $this->chaveNotaFiscalEletronica; }
    public function getCodigoRecolhimentoFethab(): ?string { return $this->codigoRecolhimentoFethab; }
    public function getResponsavelRecolhimentoFethab(): ?string { return $this->responsavelRecolhimentoFethab; }
    public function getCfopDocumentoFiscal(): ?string { return $this->cfopDocumentoFiscal; }
    public function getTipoCte(): ?int { return $this->tipoCte; }
    public function getCteReferencia(): ?string { return $this->cteReferencia; }
    public function getModalidadeImportacao(): ?int { return $this->modalidadeImportacao; }
    public function getCodigoInformacaoComplementar(): ?string { return $this->codigoInformacaoComplementar; }
    public function getInformacaoComplementar(): ?string { return $this->informacaoComplementar; }
    public function getClasseConsumo(): ?int { return $this->classeConsumo; }
    public function getTipoLigacao(): ?int { return $this->tipoLigacao; }
    public function getGrupoTensao(): ?int { return $this->grupoTensao; }
    public function getTipoAssinante(): ?int { return $this->tipoAssinante; }
    public function getKwhConsumido(): ?int { return $this->kwhConsumido; }
    public function getValorFornecidoConsumidoGasEnergia(): ?float { return $this->valorFornecidoConsumidoGasEnergia; }
    public function getValorCobradoTerceiros(): ?float { return $this->valorCobradoTerceiros; }
    public function getTipoDocumentoImportacao(): ?int { return $this->tipoDocumentoImportacao; }
    public function getNumeroAtoConcessorioDrawback(): ?string { return $this->numeroAtoConcessorioDrawback; }
    public function getNaturezaFretePisCofins(): ?int { return $this->naturezaFretePisCofins; }
    public function getCstPisCofins(): ?int { return $this->cstPisCofins; }
    public function getBaseCreditoPisCofins(): ?int { return $this->baseCreditoPisCofins; }
    public function getValorServicosItensPisCofins(): ?float { return $this->valorServicosItensPisCofins; }
    public function getBaseCalculoPisCofins(): ?float { return $this->baseCalculoPisCofins; }
    public function getAliquotaPis(): ?float { return $this->aliquotaPis; }
    public function getAliquotaCofins(): ?float { return $this->aliquotaCofins; }
    public function getChaveNfse(): ?string { return $this->chaveNfse; }
    public function getNumeroProcessoAtoConcessorio(): ?string { return $this->numeroProcessoAtoConcessorio; }
    public function getOrigemProcesso(): ?string { return $this->origemProcesso; }
    public function getDataEscrituracao(): ?\DateTime { return $this->dataEscrituracao; }
    public function getCfps(): ?int { return $this->cfps; }
    public function getNaturezaReceitaPisCofins(): ?int { return $this->naturezaReceitaPisCofins; }
    public function getCstIpi(): ?string { return $this->cstIpi; }
    public function getLancamentosScp(): ?int { return $this->lancamentosScp; }
    public function getTipoServico(): ?int { return $this->tipoServico; }
    public function getMunicipioDestino(): ?int { return $this->municipioDestino; }
    public function getPedagio(): ?float { return $this->pedagio; }
    public function getIpi(): ?float { return $this->ipi; }
    public function getIcmsSt(): ?float { return $this->icmsSt; }
    public function getClassificacaoServicos(): ?int { return $this->classificacaoServicos; }

    // Setters
    public function setCodigoEspecie(string $codigoEspecie): void { $this->codigoEspecie = $codigoEspecie; }
    public function setInscricaoFornecedor(string $inscricaoFornecedor): void { $this->inscricaoFornecedor = $inscricaoFornecedor; }
    public function setCodigoExclusaoDief(?string $codigoExclusaoDief): void { $this->codigoExclusaoDief = $codigoExclusaoDief; }
    public function setCodigoAcumulador(?string $codigoAcumulador): void { $this->codigoAcumulador = $codigoAcumulador; }
    public function setCfop(string $cfop): void { $this->cfop = $cfop; }
    public function setSegmento(?string $segmento): void { $this->segmento = $segmento; }
    public function setNumeroDocumento(int $numeroDocumento): void { $this->numeroDocumento = $numeroDocumento; }
    public function setSerie(?string $serie): void { $this->serie = $serie; }
    public function setNumeroDocumentoFinal(?int $numeroDocumentoFinal): void { $this->numeroDocumentoFinal = $numeroDocumentoFinal; }
    public function setDataEntrada(\DateTime $dataEntrada): void { $this->dataEntrada = $dataEntrada; }
    public function setDataEmissao(\DateTime $dataEmissao): void { $this->dataEmissao = $dataEmissao; }
    public function setValorContabil(float $valorContabil): void { $this->valorContabil = $valorContabil; }
    public function setValorExclusaoDief(?float $valorExclusaoDief): void { $this->valorExclusaoDief = $valorExclusaoDief; }
    public function setObservacao(?string $observacao): void { $this->observacao = $observacao; }
    public function setModalidadeFrete(?string $modalidadeFrete): void { $this->modalidadeFrete = $modalidadeFrete; }
    public function setEmitenteNotaFiscal(?string $emitenteNotaFiscal): void { $this->emitenteNotaFiscal = $emitenteNotaFiscal; }
    public function setCfopExtendidoDetalhamento(?string $cfopExtendidoDetalhamento): void { $this->cfopExtendidoDetalhamento = $cfopExtendidoDetalhamento; }
    public function setCodigoTransferenciaCredito(?string $codigoTransferenciaCredito): void { $this->codigoTransferenciaCredito = $codigoTransferenciaCredito; }
    public function setCodigoRecolhimentoIssRetido(?string $codigoRecolhimentoIssRetido): void { $this->codigoRecolhimentoIssRetido = $codigoRecolhimentoIssRetido; }
    public function setCodigoRecolhimentoIrrf(?string $codigoRecolhimentoIrrf): void { $this->codigoRecolhimentoIrrf = $codigoRecolhimentoIrrf; }
    public function setCodigoObservacao(?string $codigoObservacao): void { $this->codigoObservacao = $codigoObservacao; }
    public function setDataVistoTransfCreditoIcms(?\DateTime $dataVistoTransfCreditoIcms): void { $this->dataVistoTransfCreditoIcms = $dataVistoTransfCreditoIcms; }
    public function setFatoGeradorCrf(?string $fatoGeradorCrf): void { $this->fatoGeradorCrf = $fatoGeradorCrf; }
    public function setFatoGeradorIrrf(?string $fatoGeradorIrrf): void { $this->fatoGeradorIrrf = $fatoGeradorIrrf; }
    public function setValorFrete(?float $valorFrete): void { $this->valorFrete = $valorFrete; }
    public function setValorSeguro(?float $valorSeguro): void { $this->valorSeguro = $valorSeguro; }
    public function setValorDespesas(?float $valorDespesas): void { $this->valorDespesas = $valorDespesas; }
    public function setValorPis(?float $valorPis): void { $this->valorPis = $valorPis; }
    public function setCodigoAntecipacaoTributaria(?string $codigoAntecipacaoTributaria): void { $this->codigoAntecipacaoTributaria = $codigoAntecipacaoTributaria; }
    public function setValorCofins(?float $valorCofins): void { $this->valorCofins = $valorCofins; }
    public function setValorDareNota(?float $valorDareNota): void { $this->valorDareNota = $valorDareNota; }
    public function setAliquotaDareNota(?float $aliquotaDareNota): void { $this->aliquotaDareNota = $aliquotaDareNota; }
    public function setValorBaseCalculoIcmsSt(?float $valorBaseCalculoIcmsSt): void { $this->valorBaseCalculoIcmsSt = $valorBaseCalculoIcmsSt; }
    public function setEntradasSaidasIsentas(?float $entradasSaidasIsentas): void { $this->entradasSaidasIsentas = $entradasSaidasIsentas; }
    public function setOutrasEntradasIsentas(?float $outrasEntradasIsentas): void { $this->outrasEntradasIsentas = $outrasEntradasIsentas; }
    public function setValorTransporteIncluidoBase(?float $valorTransporteIncluidoBase): void { $this->valorTransporteIncluidoBase = $valorTransporteIncluidoBase; }
    public function setCodigoRessarcimento(?string $codigoRessarcimento): void { $this->codigoRessarcimento = $codigoRessarcimento; }
    public function setValorProdutos(float $valorProdutos): void { $this->valorProdutos = $valorProdutos; }
    public function setMunicipioOrigem(string $municipioOrigem): void { $this->municipioOrigem = $municipioOrigem; }
    public function setSituacaoNota(int $situacaoNota): void { $this->situacaoNota = $situacaoNota; }
    public function setCodigoSituacaoTributaria(?string $codigoSituacaoTributaria): void { $this->codigoSituacaoTributaria = $codigoSituacaoTributaria; }
    public function setSubSerie(?string $subSerie): void { $this->subSerie = $subSerie; }
    public function setInscricaoEstadualFornecedor(?string $inscricaoEstadualFornecedor): void { $this->inscricaoEstadualFornecedor = $inscricaoEstadualFornecedor; }
    public function setInscricaoMunicipalFornecedor(?string $inscricaoMunicipalFornecedor): void { $this->inscricaoMunicipalFornecedor = $inscricaoMunicipalFornecedor; }
    public function setCodigoOperacaoPrestacao(?string $codigoOperacaoPrestacao): void { $this->codigoOperacaoPrestacao = $codigoOperacaoPrestacao; }
    public function setValorDeduzirReceitaTributavel(?float $valorDeduzirReceitaTributavel): void { $this->valorDeduzirReceitaTributavel = $valorDeduzirReceitaTributavel; }
    public function setCompetencia(?\DateTime $competencia): void { $this->competencia = $competencia; }
    public function setOperacao(?int $operacao): void { $this->operacao = $operacao; }
    public function setNumeroParecerFiscal(?string $numeroParecerFiscal): void { $this->numeroParecerFiscal = $numeroParecerFiscal; }
    public function setDataParecerFiscal(?\DateTime $dataParecerFiscal): void { $this->dataParecerFiscal = $dataParecerFiscal; }
    public function setNumeroDeclaracaoImportacao(?string $numeroDeclaracaoImportacao): void { $this->numeroDeclaracaoImportacao = $numeroDeclaracaoImportacao; }
    public function setPossuiBeneficioFiscal(?string $possuiBeneficioFiscal): void { $this->possuiBeneficioFiscal = $possuiBeneficioFiscal; }
    public function setChaveNotaFiscalEletronica(?string $chaveNotaFiscalEletronica): void { $this->chaveNotaFiscalEletronica = $chaveNotaFiscalEletronica; }
    public function setCodigoRecolhimentoFethab(?string $codigoRecolhimentoFethab): void { $this->codigoRecolhimentoFethab = $codigoRecolhimentoFethab; }
    public function setResponsavelRecolhimentoFethab(?string $responsavelRecolhimentoFethab): void { $this->responsavelRecolhimentoFethab = $responsavelRecolhimentoFethab; }
    public function setCfopDocumentoFiscal(?string $cfopDocumentoFiscal): void { $this->cfopDocumentoFiscal = $cfopDocumentoFiscal; }
    public function setTipoCte(?int $tipoCte): void { $this->tipoCte = $tipoCte; }
    public function setCteReferencia(?string $cteReferencia): void { $this->cteReferencia = $cteReferencia; }
    public function setModalidadeImportacao(?int $modalidadeImportacao): void { $this->modalidadeImportacao = $modalidadeImportacao; }
    public function setCodigoInformacaoComplementar(?string $codigoInformacaoComplementar): void { $this->codigoInformacaoComplementar = $codigoInformacaoComplementar; }
    public function setInformacaoComplementar(?string $informacaoComplementar): void { $this->informacaoComplementar = $informacaoComplementar; }
    public function setClasseConsumo(?int $classeConsumo): void { $this->classeConsumo = $classeConsumo; }
    public function setTipoLigacao(?int $tipoLigacao): void { $this->tipoLigacao = $tipoLigacao; }
    public function setGrupoTensao(?int $grupoTensao): void { $this->grupoTensao = $grupoTensao; }
    public function setTipoAssinante(?int $tipoAssinante): void { $this->tipoAssinante = $tipoAssinante; }
    public function setKwhConsumido(?int $kwhConsumido): void { $this->kwhConsumido = $kwhConsumido; }
    public function setValorFornecidoConsumidoGasEnergia(?float $valorFornecidoConsumidoGasEnergia): void { $this->valorFornecidoConsumidoGasEnergia = $valorFornecidoConsumidoGasEnergia; }
    public function setValorCobradoTerceiros(?float $valorCobradoTerceiros): void { $this->valorCobradoTerceiros = $valorCobradoTerceiros; }
    public function setTipoDocumentoImportacao(?int $tipoDocumentoImportacao): void { $this->tipoDocumentoImportacao = $tipoDocumentoImportacao; }
    public function setNumeroAtoConcessorioDrawback(?string $numeroAtoConcessorioDrawback): void { $this->numeroAtoConcessorioDrawback = $numeroAtoConcessorioDrawback; }
    public function setNaturezaFretePisCofins(?int $naturezaFretePisCofins): void { $this->naturezaFretePisCofins = $naturezaFretePisCofins; }
    public function setCstPisCofins(?int $cstPisCofins): void { $this->cstPisCofins = $cstPisCofins; }
    public function setBaseCreditoPisCofins(?int $baseCreditoPisCofins): void { $this->baseCreditoPisCofins = $baseCreditoPisCofins; }
    public function setValorServicosItensPisCofins(?float $valorServicosItensPisCofins): void { $this->valorServicosItensPisCofins = $valorServicosItensPisCofins; }
    public function setBaseCalculoPisCofins(?float $baseCalculoPisCofins): void { $this->baseCalculoPisCofins = $baseCalculoPisCofins; }
    public function setAliquotaPis(?float $aliquotaPis): void { $this->aliquotaPis = $aliquotaPis; }
    public function setAliquotaCofins(?float $aliquotaCofins): void { $this->aliquotaCofins = $aliquotaCofins; }
    public function setChaveNfse(?string $chaveNfse): void { $this->chaveNfse = $chaveNfse; }
    public function setNumeroProcessoAtoConcessorio(?string $numeroProcessoAtoConcessorio): void { $this->numeroProcessoAtoConcessorio = $numeroProcessoAtoConcessorio; }
    public function setOrigemProcesso(?string $origemProcesso): void { $this->origemProcesso = $origemProcesso; }
    public function setDataEscrituracao(?\DateTime $dataEscrituracao): void { $this->dataEscrituracao = $dataEscrituracao; }
    public function setCfps(?int $cfps): void { $this->cfps = $cfps; }
    public function setNaturezaReceitaPisCofins(?int $naturezaReceitaPisCofins): void { $this->naturezaReceitaPisCofins = $naturezaReceitaPisCofins; }
    public function setCstIpi(?string $cstIpi): void { $this->cstIpi = $cstIpi; }
    public function setLancamentosScp(?int $lancamentosScp): void { $this->lancamentosScp = $lancamentosScp; }
    public function setTipoServico(?int $tipoServico): void { $this->tipoServico = $tipoServico; }
    public function setMunicipioDestino(?int $municipioDestino): void { $this->municipioDestino = $municipioDestino; }
    public function setPedagio(?float $pedagio): void { $this->pedagio = $pedagio; }
    public function setIpi(?float $ipi): void { $this->ipi = $ipi; }
    public function setIcmsSt(?float $icmsSt): void { $this->icmsSt = $icmsSt; }
    public function setClassificacaoServicos(?int $classificacaoServicos): void { $this->classificacaoServicos = $classificacaoServicos; }
}