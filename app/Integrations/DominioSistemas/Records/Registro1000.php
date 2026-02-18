<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\Issuer;
use App\Models\GeneralSetting;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Cache;
use App\Models\EntradasImpostosEquivalente;
use App\Models\EntradasAcumuladoresEquivalente;

/**
 * Registro 1000 - Notas Fiscais de Entrada
 * Notas Fiscais de Entrada.
 * 
 * Este registro é gerado para cada etiqueta aplicada à nota fiscal,
 * com os valores proporcionais ao valor aplicado a cada etiqueta.
 */
class Registro1000 extends RegistroBase
{
    private string $codigoEspecie;
    private array $valoresSegmento;
    private string $inscricaoFornecedor;
    private ?string $codigoExclusaoDief = null;
    private ?string $codigoAcumulador = null; // Campo 5 - ID da CategoryTag
    private string $cfop; // Campo 6 - CFOP (pode ser equivalente)
    private ?string $segmento = null;
    private int $numeroDocumento;
    private ?string $serie = null;
    private ?int $numeroDocumentoFinal = null;
    private \DateTime $dataEntrada;
    private \DateTime $dataEmissao;
    private float $valorContabil;
    private ?string $valorExclusaoDief = null;
    private ?string $observacao = null;
    private ?string $modalidadeFrete = null;
    private ?string $emitenteNotaFiscal = null;
    private ?string $cfopExtendidoDetalhamento = null;
    private ?string $codigoTransferenciaCredito = null;
    private ?string $codigoRecolhimentoIssRetido = null;
    private ?string $codigoRecolhimentoIrrf = null;
    private ?string $codigoObservacao = null;
    private ?\DateTime $dataVistoTransfCreditoIcms = null;
    private ?string $fatoGeradorCrf = null;
    private ?string $fatoGeradorIrrf = null;
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
    private ?string $possuiBeneficioFiscal = null;
    private ?string $chaveNotaFiscalEletronica = null;
    private ?string $codigoRecolhimentoFethab = null;
    private ?string $responsavelRecolhimentoFethab = null;
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
    private ?int $classificacaoServicosTipoEfdReinf = null; // Campo 92
    private ?int $classificacaoServicosIndicativoEfdReinf = null; // Campo 93
    private ?string $numeroDocumentoArrecadacao = null; // Campo 94
    private ?int $tipoTitulo = null; // Campo 95
    private ?string $identificacao = null; // Campo 96
    private ?float $icmsDesonerado = null; // Campo 97
    private ?float $ipiDevolucao = null; // Campo 98

    /**
     * @var float Fator de proporcionalidade baseado no valor da etiqueta
     */
    private float $fatorProporcionalidade = 1.0;

    /**
     * @var int|null ID da tag aplicada à nota fiscal
     */
    private ?int $tagId = null;

    /**
     * @var string|null CFOP específico para sobrescrever o primeiro CFOP da nota
     */
    private ?string $cfopEspecifico = null;

    /**
     * @var NotaFiscalEletronica Referência à nota fiscal para acesso às tags
     */
    private NotaFiscalEletronica $notaFiscal;

    /**
     * Cache estático para armazenar os CFOPs equivalentes já consultados
     * Key: issuer_id_tag_id_cfop
     * Value: cfop_entrada equivalente ou null
     *
     * @var array
     */
    private static array $cfopEquivalenteCache = [];

    /**
     * Cache estático para armazenar os CFOPs acumuladores já consultados
     * Key: issuer_id_tag_id_cfop
     * Value: cfop_entrada equivalente ou null
     *
     * @var array
     */
    private static array $acumuladorEquivalenteCache = [];

    public function __construct(
        NotaFiscalEletronica $notaFiscal,
        array $valoresSegmento,
        Issuer $issuer,
        ?int $tagId = null,
        ?int $segmento = null
    ) {
        // Armazena a referência à nota fiscal e o ID da tag para uso posterior
        $this->notaFiscal = $notaFiscal;
        $this->tagId = $tagId;
        $this->valoresSegmento = $valoresSegmento;

        // Extrai os dados do XML da nota fiscal usando o método da classe base
        $xmlData = $this->extrairDadosDoXml($notaFiscal, [
            'emit' => ['emit', 'emitente', 'Emitente'],
            'enderEmit' => ['enderEmit', 'ender_emit', 'EnderecoEmitente'],
            'dest' => ['dest', 'destinatario', 'Destinatario'],
            'enderDest' => ['enderDest', 'ender_dest', 'EnderecoDestinatario'],
            'transp' => ['transp', 'transporte', 'Transporte'],
            'cobr' => ['cobr', 'cobranca', 'Cobranca'],
            'infAdic' => ['infAdic'],
            'total' => ['total', 'totais', 'Totais'],
        ]);
        // ============================================================
        // CAMPO 1 - Identificação do registro: Fixo '1000'
        // Definido no método getTipoRegistro()
        // ============================================================

        // Campo 2 - Código da espécie: 36 = NF-e (Nota Fiscal Eletrônica)
        $this->codigoEspecie = '36';

        // Campo 3 - Inscrição fornecedor: CNPJ/CPF/CEI/Outros/CAEPF do fornecedor
        $this->inscricaoFornecedor = $this->definirCnpjFornecedor($xmlData, $notaFiscal);

        // Campo 4 - Código de Exclusão da DIEF: 0 = Não exclui
        $this->codigoExclusaoDief = 0;

        // Campo 6 - CFOP: Código Fiscal de Operações e Prestações (pode ser equivalente conforme configuração)
    
        $this->cfop = $this->obterCfopEquivalente($notaFiscal, $issuer, $tagId, $xmlData['emit']['enderEmit']['UF']);


        // Campo 5 - Código do acumulador: ID da CategoryTag (etiqueta de classificação)
        $this->codigoAcumulador = $this->obterAcumuladorEquivalente($notaFiscal, $issuer, $tagId, $this->cfop);

        // Campo 7 - Segmento
        $this->segmento = $segmento;

        // Campo 8 - Número do documento: Número da NF-e
        $this->numeroDocumento = (int)($notaFiscal->nNF ?? 0);

        // Campo 9 - Série: Série da NF-e
        $this->serie = $notaFiscal->serie ?? null;

        // Campo 10 - Número do documento final: 0 para nota única (usado para notas em série)
        $this->numeroDocumentoFinal = 0;

        // Campo 11 - Data da entrada: Data de entrada da mercadoria no estabelecimento
        $this->dataEntrada = $this->converterParaDateTime($notaFiscal->data_entrada ?? now());

        // Campo 12 - Data emissão: Data de emissão da NF-e pelo fornecedor
        $this->dataEmissao = $this->converterParaDateTime($notaFiscal->data_emissao ?? now());

        // Campo 13 - Valor contábil: Valor total da NF-e (valor da operação)
        $this->valorContabil = $this->valoresSegmento['valor_produtos'] - $this->valoresSegmento['valor_desconto'];

        // Campo 14 - Valor da exclusão da DIEF: Não informado (campo opcional)
        $this->valorExclusaoDief = '';

        // Campo 15 - Observação: Informações de interesse do fisco (infAdFisco do XML)
        $this->observacao = $xmlData['infAdic']['infAdFisco'] ?? str_replace('|', '-', $xmlData['infAdic']['infAdFisco'] ?? '');

        // Campo 16 - Modalidade do frete: C=CIF, F=FOB, T=Terceiros, R=Remetente, D=Destinatário, S=Sem frete
        $this->modalidadeFrete = $this->checkTipoFrete($notaFiscal->modFrete) ?? null;

        // Campo 17 - Emitente da nota fiscal: P=Próprio, T=Terceiros
        $this->emitenteNotaFiscal = $this->checkNotaEmitida($notaFiscal);

    
        // Campo 18 - CFOP estendido/detalhamento: Apenas para estado de SE
        $this->cfopExtendidoDetalhamento = 0;

        // Campo 19 - Código da transferência de crédito: Apenas para estado de RS
        $this->codigoTransferenciaCredito = 0;

        // Campo 20 - Código do Recolhimento do ISS Retido: Não informado (campo opcional)
        $this->codigoRecolhimentoIssRetido = '';

        // Campo 21 - Código do Recolhimento do IRRF: Não informado (campo opcional)
        $this->codigoRecolhimentoIrrf = '';

        // Campo 22 - Código da observação: Não informado (campo opcional)
        $this->codigoObservacao = '';

        // Campo 23 - Data do visto notas de transf. Crédito ICMS: Apenas para estado de MG
        $this->dataVistoTransfCreditoIcms = $this->converterParaDateTime($notaFiscal->data_emissao);

        // Campo 24 - Fato gerador da CRF: E=Emissão, P=Pagamento
        $this->fatoGeradorCrf = 'E';

        // Campo 25 - Fato gerador do IRRF: E=Emissão, P=Pagamento
        $this->fatoGeradorIrrf = 'E';

        // ============================================================
        // CAMPOS 26-31, 39, 90, 91, 98 - Valores com proporcionalidade
        // Definidos no método aplicarProporcionalidadeValores()
        // ============================================================
        $this->aplicarProporcionalidadeValores($xmlData, $notaFiscal);

        // Campo 32 - Valor calculado referente a DARE da nota: Apenas para estado SE
        // $this->valorDareNota = null;

        // Campo 33 - Alíquota do valor calculado referente a DARE da nota: Apenas para estado SE
        // $this->aliquotaDareNota = null;

        // Campo 34 - Valor da base de cálculo do ICMS ST: 0=inf. Complementares, 1=Quadro calculado, 2=Apurado pelo informante
        // $this->valorBaseCalculoIcmsSt = null;

        // Campo 35 - Entradas cuja saídas é isenta: Apenas para estado MG
        // $this->entradasSaidasIsentas = null;

        // Campo 36 - Outras entradas isentas: Apenas para estado MG
        // $this->outrasEntradasIsentas = null;

        // Campo 37 - Valor transporte incluído na base: Apenas para estado MG
        // $this->valorTransporteIncluidoBase = null;

        // Campo 38 - Código de ressarcimento: Não informado (campo opcional)
        // $this->codigoRessarcimento = null;

        // Campo 39 - Valor produtos: Definido no método aplicarProporcionalidadeValores()

        // Campo 40 - Município Origem: Código IBGE do município de origem (cMun do emitente)
        $this->municipioOrigem = '0';

        // Campo 41 - Situação da Nota: 0=Regular, 1=Regular Extemporâneo, 2=Cancelado, 6=Complementar, 7=Denegado, 8=Inutilizada, 9=Regime Especial, 10=Complementar Extemporâneo
        $this->situacaoNota = '0';

        // Campo 42 - Código da situação tributária: Não informado (campo opcional)
        $this->codigoSituacaoTributaria = '0';

        // Campo 43 - Sub série: Não informado (campo opcional)
        $this->subSerie = '0';


        // Campo 44 - Inscrição estadual do fornecedor: IE do emitente da NF-e
        $this->inscricaoEstadualFornecedor = $this->checkIsImportacao($notaFiscal) ? $xmlData['dest']['IE'] ?? '' : $xmlData['emit']['IE'] ?? '';

        // Campo 45 - Inscrição municipal do fornecedor: IM do emitente da NF-e
        $this->inscricaoMunicipalFornecedor = $this->checkIsImportacao($notaFiscal) ? $xmlData['dest']['IM'] ?? '' : $xmlData['emit']['IM'] ?? '';

        // Campo 46 - Código da operação e prestação: Não informado (campo opcional)
        $this->codigoOperacaoPrestacao = '';

        // Campo 47 - Valor a ser deduzido da receita tributável: Não informado (campo opcional)
        $this->valorDeduzirReceitaTributavel = 0;

        // Campo 48 - Competência: Não informado (campo opcional)

        $this->competencia = $notaFiscal->data_emissao;

        // Campo 49 - Operação: Apenas para estado PA
        $this->operacao = null;

        // Campo 50 - Número do parecer fiscal: Não informado (campo opcional)
        $this->numeroParecerFiscal = '';

        // Campo 51 - Data do parecer fiscal: Não informado (campo opcional)
        $this->dataParecerFiscal = $this->converterParaDateTime($notaFiscal->data_entrada ?? now());

        // Campo 52 - Número da declaração de Importação: Não informado (campo opcional)
        $this->numeroDeclaracaoImportacao = '';

        // Campo 53 - Possui benefício fiscal: S=Sim, N=Não
        $this->possuiBeneficioFiscal = 'N';

        // Campo 54 - Chave da nota fiscal eletrônica: Chave de acesso da NF-e (44 dígitos)
        $this->chaveNotaFiscalEletronica = $notaFiscal->chave ?? null;

        // Campo 55 - Código de recolhimento do FETHAB: Não informado (campo opcional)
        $this->codigoRecolhimentoFethab = null;

        // Campo 56 - Responsável pelo recolhimento do FETHAB: E=Empresa, C=Cliente
        $this->responsavelRecolhimentoFethab = null;


        // Campo 57 - CFOP documento fiscal: Não informado (campo opcional)
        
        $this->cfopDocumentoFiscal = $this->valoresSegmento['cfop'];

        // Campo 58 - Tipo de CT-e: 0=Normal, 1=Complemento de valores, 2=Anulação de débito
        // $this->tipoCte = null;

        // Campo 59 - CT-e referência: Não informado (campo opcional)
        // $this->cteReferencia = null;

        // Campo 60 - Modalidade da importação: 1=Com crédito, 2=Compensação, 3=Regime especial, 4=Sem crédito, 5=Outras
        // $this->modalidadeImportacao = null;

        // Campo 61 - Código da informação complementar: Não informado (campo opcional)
        // $this->codigoInformacaoComplementar = null;

        // Campo 62 - Informação complementar: Informações complementares de interesse do contribuinte (infCpl do XML)
        $this->informacaoComplementar = '';

        // Campo 63 - Classe de consumo: Não informado (campo opcional - para energia/gás)
        // $this->classeConsumo = null;

        // Campo 64 - Tipo de ligação: Não informado (campo opcional - para energia)
        // $this->tipoLigacao = null;

        // Campo 65 - Grupo de tensão: Não informado (campo opcional - para energia)
        // $this->grupoTensao = null;

        // Campo 66 - Tipo de assinante: Não informado (campo opcional - para telecomunicações)
        // $this->tipoAssinante = null;

        // Campo 67 - KWH consumido: Não informado (campo opcional - para energia)
        // $this->kwhConsumido = null;

        // Campo 68 - Valor fornecido / consumido de gás ou energia elétrica: Não informado
        // $this->valorFornecidoConsumidoGasEnergia = null;

        // Campo 69 - Valor cobrado de terceiros: Não informado (campo opcional)
        // $this->valorCobradoTerceiros = null;

        // Campo 70 - Tipo do documento de importação: 10=Declaração de Importação, 1=Declaração Simplificada
        // $this->tipoDocumentoImportacao = null;

        // Campo 71 - Número do Ato Concessório do regime Drawback: Não informado (campo opcional)
        // $this->numeroAtoConcessorioDrawback = null;

        // Campo 72 - Natureza do frete PIS/COFINS: 0-9 conforme tabela (para modelos 08, 08B, 09, 10, 11, 26, 27, 57)
        // $this->naturezaFretePisCofins = null;

        // Campo 73 - CST PIS/COFINS: Código 50-99 para modelos específicos
        // $this->cstPisCofins = null;

        // Campo 74 - Base do crédito PIS/COFINS: 03, 07, 13, 14 (para modelos específicos)
        // $this->baseCreditoPisCofins = null;

        // Campo 75 - Valor serviços / itens PIS/COFINS: Não informado (para modelos específicos)
        // $this->valorServicosItensPisCofins = null;

        // Campo 76 - Base de cálculo PIS/COFINS: Não informado (para modelos específicos)
        // $this->baseCalculoPisCofins = null;

        // Campo 77 - Alíquota de PIS: Não informado (campo opcional)
        // $this->aliquotaPis = null;

        // Campo 78 - Alíquota de COFINS: Não informado (campo opcional)
        // $this->aliquotaCofins = null;

        // Campo 79 - Chave de NFSe: Não informado (campo opcional)
        // $this->chaveNfse = null;

        // Campo 80 - Número do processo ou ato concessório: Não informado (para natureza frete 1, 3, 4, 5)
        // $this->numeroProcessoAtoConcessorio = null;

        // Campo 81 - Origem do processo: 1=Justiça Federal, 3=SRF, 9=Outros
        // $this->origemProcesso = null;

        // Campo 82 - Data da escrituração: Data quando situação for Documento Extemporâneo
        $this->dataEscrituracao = isset($notaFiscal->data_entrada) ? $this->converterParaDateTime($notaFiscal->data_entrada) : null;

        // Campo 83 - CFPS: Apenas para estado DF (Código Fiscal de Prestação de Serviços)
        // $this->cfps = null;

        // Campo 84 - Natureza da receita PIS/COFINS: Não informado (para modelos 07, 08, 08B, 09, 10, 11, 57)
        // $this->naturezaReceitaPisCofins = null;

        // Campo 85 - CST IPI: 00-49 conforme tabela CST IPI (para modelos 55, 01, 1B, 04 com regime especial)
        $this->cstIpi = $this->ajustaIpi($this->valoresSegmento['valor_ipi'], $notaFiscal, $tagId, $issuer);

        // Campo 86 - Lançamentos de SCP: Código SCP (Sociedade em Conta de Participação)
        // $this->lancamentosScp = null;

        // Campo 87 - Tipo de serviço: 1=Transporte de cargas, 2=Transporte de passageiros
        // $this->tipoServico = null;

        // Campo 88 - Município destino: Apenas para CT-e com CFOP iniciando em 2-XXX e imposto 145-DIFAL
        // $this->municipioDestino = null;

        // Campo 89 - Pedágio: Não informado (campo opcional)
        // $this->pedagio = null;

        // Campo 90 - IPI: Definido no método aplicarProporcionalidadeValores()
        // Campo 91 - ICMS ST: Definido no método aplicarProporcionalidadeValores()

        // Campo 92 - Classificação de Serviços Prestados - Tipo de serviço - EFD-Reinf: Não informado
        // $this->classificacaoServicosTipoEfdReinf = null;

        // Campo 93 - Classificação de Serviços Prestados - Indicativo de Prestação de Serviço - EFD-Reinf: Não informado
        // $this->classificacaoServicosIndicativoEfdReinf = null;

        // Campo 94 - Número do documento de arrecadação: Não informado (campo opcional)
        // $this->numeroDocumentoArrecadacao = null;

        // Campo 95 - Tipo do título: Não informado (campo opcional)
        // $this->tipoTitulo = null;

        // Campo 96 - Identificação: Até 60 caracteres (campo opcional)
        // $this->identificacao = null;

        // Campo 97 - ICMS Desonerado: Não informado (campo opcional)
        // $this->icmsDesonerado = null;

        // Campo 98 - IPI Devolução: Definido no método aplicarProporcionalidadeValores()
    }

    /**
     * Obtém o CFOP equivalente baseado nas etiquetas e configurações
     * Utiliza cache estático para otimizar consultas repetidas
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @param Issuer $issuer
     * @param int|null $tagId
     * @param string|null $ufEmitente
     * @return string CFOP original ou equivalente
     */
    private function obterCfopEquivalente(NotaFiscalEletronica $notaFiscal, Issuer $issuer, ?int $tagId, ?string $ufEmitente): string
    {
        // Early return: sem tag, retorna CFOP original
        $cfopOriginal = $this->valoresSegmento['cfop'];


        if (!$tagId) {
            return $cfopOriginal;
        }

        // Determina configurações e tipo de documento
        $verificarUf = GeneralSetting::getValue(
            name: 'configuracoes_gerais',
            key: 'verificar_uf_emitente_destinatario',
            default: false,
            issuerId: $issuer->id
        );


        $tipoDocumento = $this->determinarTipoDocumento($notaFiscal, $issuer);
        $ufIssuer = $issuer?->municipio?->sigla;

        // Gera chave de cache (inclui UF quando verificação está ativa)
        $cacheKey = $this->gerarChaveCacheCfop(
            $issuer->id,
            $tagId,
            $cfopOriginal,
            $tipoDocumento,
            $verificarUf ? $ufIssuer : null,
            $verificarUf ? $ufEmitente : null
        );


        // Verifica cache estático
        if (isset(self::$cfopEquivalenteCache[$cacheKey])) {
            return self::$cfopEquivalenteCache[$cacheKey] ?? $cfopOriginal;
        }

        // Busca grupo com a tag específica
        $grupoEncontrado = $this->buscarGrupoPorTag($issuer, $tagId, $tipoDocumento);

        if (!$grupoEncontrado) {
            self::$cfopEquivalenteCache[$cacheKey] = null;
            return $cfopOriginal;
        }

        // Busca CFOP equivalente no grupo
        $cfopResultado = $this->buscarCfopEquivalenteNoGrupo(
            $grupoEncontrado,
            $cfopOriginal,
            $verificarUf,
            $ufIssuer,
            $ufEmitente
        );


        // Armazena em cache e retorna
        self::$cfopEquivalenteCache[$cacheKey] = $cfopResultado;

        return $cfopResultado ?? $cfopOriginal;
    }

    private function ajustaIpi($valor, NotaFiscalEletronica $notaFiscal, $tagId, $issuer)
    {
        $campo = '';

        $tag = $notaFiscal->tags->where('id', $tagId)->first();

        $isIndustria = in_array('industria', $issuer->atividade);


        if ($tag) {

            $tagToConvert = $this->getTagToConvert($tag, $issuer, ipi: true);

            $valor == 0 && $isIndustria && $tagToConvert ? $campo = '49' : $campo = '00';

            $valor > 0 && $tagToConvert ? $campo = '49' : $campo = '00';
        }

        return $campo;
    }

    private function getTagToConvert($tag, $issuer, $icms = false, $ipi = false)
    {

        $tagsToConverter = Cache::remember('entradas_impostos_equivalentes_' . $issuer->id, 300, function () use ($issuer) {
            return EntradasImpostosEquivalente::where('issuer_id', $issuer->id)->get();
        });

        if ($icms) {
            $tagsToConverter = $tagsToConverter->where('status_icms', true);
        }

        if ($ipi) {
            $tagsToConverter = $tagsToConverter->where('status_ipi', true);
        }


        foreach ($tagsToConverter as $tagConverter) {

            if ($tagConverter->tag == intval($tag->code)) {

                return true;
            }
        }

        return false;
    }

    /**
     * Gera a chave de cache para CFOP equivalente
     *
     * @param int $issuerId
     * @param int $tagId
     * @param string $cfop
     * @param string $tipoDocumento
     * @param string|null $ufIssuer
     * @param string|null $ufEmitente
     * @return string
     */
    private function gerarChaveCacheCfop(
        int $issuerId,
        int $tagId,
        string $cfop,
        string $tipoDocumento,
        ?string $ufIssuer = null,
        ?string $ufEmitente = null
    ): string {
        $key = "{$issuerId}_{$tagId}_{$cfop}_{$tipoDocumento}";

        if ($ufIssuer !== null || $ufEmitente !== null) {
            $key .= "_{$ufIssuer}_{$ufEmitente}";
        }

        return $key;
    }

    /**
     * Busca o grupo de CFOP equivalente que contém a tag especificada
     *
     * @param Issuer $issuer
     * @param int $tagId
     * @param string $tipoDocumento
     * @return \App\Models\GrupoEntradaCfopEquivalente|null
     */
    private function buscarGrupoPorTag(Issuer $issuer, int $tagId, string $tipoDocumento): ?\App\Models\GrupoEntradaCfopEquivalente
    {
        $grupos = \App\Models\GrupoEntradaCfopEquivalente::getAllCached(
            $issuer->id,
            $issuer->tenant_id,
            $tipoDocumento
        );

        foreach ($grupos as $grupo) {
            $tags = $grupo->tags ?? [];
            if (is_array($tags) && in_array($tagId, $tags)) {
                return $grupo;
            }
        }

        return null;
    }

    /**
     * Busca o CFOP equivalente dentro de um grupo
     *
     * @param \App\Models\GrupoEntradaCfopEquivalente $grupo
     * @param string $cfopOriginal
     * @param bool $verificarUf
     * @param string|null $ufIssuer
     * @param string|null $ufEmitente
     * @return string|null
     */
    private function buscarCfopEquivalenteNoGrupo(
        \App\Models\GrupoEntradaCfopEquivalente $grupo,
        string $cfopOriginal,
        bool $verificarUf,
        ?string $ufIssuer,
        ?string $ufEmitente
    ): ?string {
        foreach ($grupo->cfopsEquivalentes as $cfopEquivalente) {
            $valores = $this->normalizarValoresCfop($cfopEquivalente->valores);

            if (!in_array($cfopOriginal, $valores)) {
                continue;
            }

            // Se não precisa verificar UF, retorna o CFOP de entrada
            if (!$verificarUf) {
                return $cfopEquivalente->cfop_entrada ?? $cfopOriginal;
            }

            // Verifica se a regra de UF se aplica
            if ($this->verificarRegraUf($cfopEquivalente->uf_diferente, $ufIssuer, $ufEmitente)) {
                return $cfopEquivalente->cfop_entrada;
            }
        }

        return null;
    }

    /**
     * Normaliza os valores de CFOP para array
     *
     * @param mixed $valores
     * @return array
     */
    private function normalizarValoresCfop($valores): array
    {
        if (is_string($valores)) {
            return explode(',', $valores);
        }

        return (array) ($valores ?? []);
    }

    /**
     * Verifica se a regra de UF é atendida
     *
     * @param bool $ufDiferente
     * @param string|null $ufIssuer
     * @param string|null $ufEmitente
     * @return bool
     */
    private function verificarRegraUf(bool $ufDiferente, ?string $ufIssuer, ?string $ufEmitente): bool
    {
        // uf_diferente = true: aplica quando issuer e emitente têm UFs diferentes
        // uf_diferente = false: aplica quando issuer e emitente têm a mesma UF
        $ufsDiferentes = ($ufIssuer !== $ufEmitente);

        return $ufDiferente === $ufsDiferentes;
    }

    /**
     * Obtém o CFOP equivalente baseado nas etiquetas e configurações
     * Utiliza cache estático para otimizar consultas repetidas
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @param Issuer $issuer
     * @param int|null $tagId
     * @return string CFOP original ou equivalente
     */
    private function obterAcumuladorEquivalente(NotaFiscalEletronica $notaFiscal, Issuer $issuer, ?int $tagId, int $cfopOriginal): string
    {

        // Determina o tipo de documento para filtrar os CFOPs equivalentes corretos
        $tipoDocumento = $this->determinarTipoDocumento($notaFiscal, $issuer);

        // Gera a chave do cache incluindo o tipo de documento
        $cacheKey = "{$issuer->id}_{$tagId}_{$cfopOriginal}_{$tipoDocumento}";


        // Verifica se já está em cache
        if (isset(self::$acumuladorEquivalenteCache[$cacheKey])) {
            return self::$acumuladorEquivalenteCache[$cacheKey] ?? $cfopOriginal;
        }


        $acumuladores = Cache::remember('acumuladores_issuer_' . $tipoDocumento . '_' . $issuer->id, 1800, function () use ($tagId, $tipoDocumento, $issuer) {
            return EntradasAcumuladoresEquivalente::where('tipo', $tipoDocumento)
                ->where('issuer_id', $issuer->id)
                ->get();
        });


        // Verifica se o CFOP original está em algum dos registros
        foreach ($acumuladores as $acumulador) {

            if (count($acumulador->cfops) > 0) {
                if (in_array($tagId, $acumulador->valores) && in_array($cfopOriginal, $acumulador->cfops)) {
                    self::$acumuladorEquivalenteCache[$cacheKey] = $acumulador->etiqueta_entrada;
                    return $acumulador->etiqueta_entrada;
                }
            }

            if (in_array($tagId, $acumulador->valores)) {
                self::$acumuladorEquivalenteCache[$cacheKey] = $acumulador->etiqueta_entrada;

                return $acumulador->etiqueta_entrada;
            }
        }

        // Se não encontrou o CFOP nos equivalentes, retorna o original
        self::$acumuladorEquivalenteCache[$cacheKey] = null;
        return $cfopOriginal;
    }

    /**
     * Determina o tipo de documento para filtrar os CFOPs equivalentes
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @param Issuer $issuer
     * @return string Tipo do documento (nfe-entrada-propria, nfe-entrada-terceiro, etc.)
     */
    private function determinarTipoDocumento(NotaFiscalEletronica $notaFiscal, Issuer $issuer): string
    {
        // Verifica se é entrada própria (emitente = issuer) ou entrada de terceiros
        $isEntradaPropria = ($notaFiscal->emitente_cnpj === $issuer->cnpj);

        return $isEntradaPropria ? 'nfe-entrada-propria' : 'nfe-entrada-terceiro';
    }

    /**
     * Traduz o código numérico da modalidade do frete para a letra correspondente.
     *
     * @param int|string $modFrete Código da modalidade do frete (0-9).
     * @return string Letra correspondente ao tipo de frete:
     *                C = Conta do emitente (0)
     *                F = Conta do destinatário (1)
     *                T = Conta de terceiros (2)
     *                R = Remetente (3)
     *                D = Destinatário (4)
     *                S = Sem frete (9)
     */
    private function checkTipoFrete($modFrete)
    {
        $texto = '';
        switch ($modFrete) {
            case 0:
                $texto = 'C';
                break;
            case 1:
                $texto = 'F';
                break;
            case 2:
                $texto = 'T';
                break;
            case 3:
                $texto = 'R';
                break;
            case 4:
                $texto = 'D';
                break;
            case 9:
                $texto = 'S';
                break;
        }

        return $texto;
    }

    /**
     * Verifica se a nota fiscal foi emitida (tpNf == 1) ou própria (tpNf == 0)
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @return string 'T' se tpNf == 1, 'P' caso contrário
     */
    private function checkNotaEmitida(NotaFiscalEletronica $notaFiscal): string
    {
        if ($notaFiscal->tpNf == 1) {
            return 'T';
        }

        // Default return for tpNf == 0 or any other value
        return 'P';
    }


    /**
     * Define o CNPJ do fornecedor baseado no tipo de operação (nacional ou importação)
     *
     * @param array $xmlData
     * @param NotaFiscalEletronica $notaFiscal
     * @return string
     */
    private function definirCnpjFornecedor(array $xmlData, NotaFiscalEletronica $notaFiscal): string
    {
        $isImportacao = $this->checkIsImportacao($notaFiscal);

        if ($isImportacao) {
            // Para importação, usa CNPJ genérico
            return '00000000000000';
        }

        // Para operação nacional, usa o CNPJ do emitente
        return $xmlData['emit']['CNPJ'] ?? $xmlData['emit']['CPF'] ?? $notaFiscal->emitente_cnpj ?? '';
    }

    /**
     * Verifica se a nota fiscal refere-se a uma operação de importação.
     *
     * @param NotaFiscalEletronica $notaFiscal Instância da nota fiscal a ser verificada.
     * @return bool Retorna true quando o CFOP inicia com 3 (indicando importação), false caso contrário.
     */
    private function checkIsImportacao(NotaFiscalEletronica $notaFiscal): bool
    {
        // Verifica se é uma nota de importação pelo CFOP
        // CFOPs de importação começam com 3 (ex: 3101, 3201, 3202, 3205, 3206, 3207, 3208, 3209, 3211, 3251, 3551, 3667)
        $cfop = $this->valoresSegmento['cfop'];
        return strpos($cfop, '3') === 0;
    }

    /**
     * Aplica o fator de proporcionalidade aos valores da nota fiscal
     *
     * @param array $xmlData
     * @param NotaFiscalEletronica $notaFiscal
     * @return void
     */
    private function aplicarProporcionalidadeValores(array $xmlData, NotaFiscalEletronica $notaFiscal): void
    {
        // Campo 26 - Valor do frete: Valor do frete da NF-e (aplicado proporcionalidade conforme valor da etiqueta)
        $this->valorFrete = $this->aplicarProporcionalidade((float)($this->valoresSegmento['valor_frete'] ?? 0));

        // Campo 27 - Valor do seguro: Valor do seguro da NF-e (aplicado proporcionalidade conforme valor da etiqueta)
        $this->valorSeguro = $this->aplicarProporcionalidade((float)($this->valoresSegmento['valor_seguro'] ?? 0));

        // Campo 28 - Valor das despesas: Valor das despesas acessórias (vOutro) (aplicado proporcionalidade conforme valor da etiqueta)
        $this->valorDespesas = $this->aplicarProporcionalidade((float)($this->valoresSegmento['valor_outro'] ?? 0));

        // Campo 29 - Valor do PIS: Valor do PIS da NF-e (aplicado proporcionalidade conforme valor da etiqueta)
        $this->valorPis = $this->aplicarProporcionalidade((float)($this->valoresSegmento['valor_pis'] ?? 0));

        // Campo 31 - Valor do COFINS: Valor do COFINS da NF-e (aplicado proporcionalidade conforme valor da etiqueta)
        $this->valorCofins = $this->aplicarProporcionalidade((float)($this->valoresSegmento['valor_cofins'] ?? 0));

        // Campo 39 - Valor produtos: Valor total dos produtos (vProd) (aplicado proporcionalidade conforme valor da etiqueta)
        $this->valorProdutos = $this->aplicarProporcionalidade((float)($this->valoresSegmento['valor_produtos'] ?? 0));

        // Valor do IPI para cálculo dos campos 90 e 98
        $valorIpi = (float)($this->valoresSegmento['valor_ipi'] ?? 0);

        dd($valorIpi);
        // Campo 90 - IPI: Valor do IPI - só aplica o valor se a categoria da tag estiver marcada como is_devolucao = true
        $this->ipi = $this->calcularIpi($valorIpi);

        // Campo 91 - ICMS ST: Valor do ICMS ST (aplicado proporcionalidade conforme valor da etiqueta)
        $this->icmsSt = $this->aplicarProporcionalidade((float)($this->valoresSegmento['valor_st'] ?? 0));

        // Campo 98 - IPI Devolução: Valor do IPI para devolução - só aplica se a categoria NÃO estiver marcada como is_devolucao (is_devolucao = false)
        $this->ipiDevolucao = $this->calcularIpiDevolucao($valorIpi);
    }

    /**
     * Verifica se a categoria da tag está marcada como devolução
     * Utiliza as tags já carregadas na nota fiscal via eager loading para evitar consultas N+1
     *
     * @return bool|null Retorna true se for devolução, false se não for, null se não conseguir determinar
     */
    private function isCategoriaDevolucao(): ?bool
    {
        // Se não há tag associada, retorna null (não é possível determinar)
        if (!$this->tagId) {
            return null;
        }

        // Busca a tag através do relacionamento já carregado na nota fiscal
        // O relacionamento 'tagged' já carrega a tag via eager loading, e a tag já carrega a categoria
        $tagged = $this->notaFiscal->tagged->firstWhere('tag_id', $this->tagId);

        // Se não encontrou o registro tagged, retorna null
        if (!$tagged || !$tagged->tag) {
            return null;
        }

        $tag = $tagged->tag;

        // Se a tag não tem categoria carregada, retorna null
        if (!$tag->category) {
            return null;
        }

        // Retorna o valor de is_devolucao da categoria
        return $tag->category->is_devolucao === true;
    }

    /**
     * Calcula o valor do IPI (Campo 90) aplicando proporcionalidade apenas se a categoria for de devolução
     * Utiliza as tags já carregadas na nota fiscal via eager loading para evitar consultas N+1
     *
     * @param float $valorIpi
     * @return float
     */
    private function calcularIpi(float $valorIpi): float
    {
        $isDevolucao = $this->isCategoriaDevolucao();

        // Só aplica o IPI se a categoria estiver marcada como is_devolucao = true
        if ($isDevolucao === true) {
            return $this->aplicarProporcionalidade($valorIpi);
        }

        // Para categorias que não são de devolução ou não foi possível determinar, retorna 0
        return 0;
    }

    /**
     * Calcula o valor do IPI Devolução (Campo 98) aplicando proporcionalidade apenas se a categoria NÃO for de devolução
     * Utiliza as tags já carregadas na nota fiscal via eager loading para evitar consultas N+1
     *
     * @param float $valorIpi
     * @return float
     */
    private function calcularIpiDevolucao(float $valorIpi): float
    {
        $isDevolucao = $this->isCategoriaDevolucao();

        // Só aplica o IPI Devolução se a categoria NÃO estiver marcada como is_devolucao (ou seja, is_devolucao = false)
        if ($isDevolucao === false) {
            return $this->aplicarProporcionalidade($valorIpi);
        }

        // Para categorias de devolução ou não foi possível determinar, retorna 0
        return 0;
    }

    /**
     * Aplica o fator de proporcionalidade a um valor
     *
     * @param float $valor
     * @return float
     */
    private function aplicarProporcionalidade(float $valor): float
    {
        return round($valor * $this->fatorProporcionalidade, 2);
    }

    /**
     * Define o fator de proporcionalidade baseado no valor da etiqueta
     *
     * @param float $fator
     * @return void
     */
    public function setFatorProporcionalidade(float $fator): void
    {
        $this->fatorProporcionalidade = $fator;
    }

    /**
     * Define o CFOP específico para o registro
     * Sobrescreve o CFOP extraído automaticamente da nota fiscal
     *
     * @param string $cfop
     * @return void
     */
    public function setCfop(string $cfop): void
    {
        $this->cfop = $cfop;
    }

    /**
     * Define o código do acumulador para o registro
     *
     * @param string|null $codigoAcumulador
     * @return void
     */
    public function setCodigoAcumulador(?string $codigoAcumulador): void
    {
        $this->codigoAcumulador = $codigoAcumulador;
    }



    /**
     * Converte uma data para DateTime
     *
     * @param mixed $data
     * @return \DateTime
     */
    private function converterParaDateTime($data): \DateTime
    {
        if ($data instanceof \DateTime) {
            return $data;
        }

        if (is_string($data)) {
            return new \DateTime($data);
        }

        return new \DateTime();
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
            $this->formatarCampo($this->numeroDocumentoFinal, null, 'N'), // Campo 10: Número do documento final
            $this->formatarCampo($this->dataEntrada, null, 'X'), // Campo 11: Data da entrada
            $this->formatarCampo($this->dataEmissao, null, 'X'), // Campo 12: Data emissão
            $this->formatarCampo($this->valorContabil, null, 'D'), // Campo 13: Valor contábil
            $this->formatarCampo($this->valorExclusaoDief, null, 'C'), // Campo 14: Valor da exclusão da DIEF
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
            $this->formatarCampo($this->codigoAntecipacaoTributaria, null, 'N'), // Campo 30: Código que identifica o tipo de Antecipação Tributária
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
            $this->formatarCampo($this->classificacaoServicosTipoEfdReinf, null, 'N'), // Campo 92: Classificação de Serviços Prestados - Tipo de serviço - EFD-Reinf
            $this->formatarCampo($this->classificacaoServicosIndicativoEfdReinf, null, 'N'), // Campo 93: Classificação de Serviços Prestados - Indicativo de Prestação de Serviço - EFD-Reinf
            $this->formatarCampo($this->numeroDocumentoArrecadacao, null, 'C'), // Campo 94: Número do documento de arrecadação
            $this->formatarCampo($this->tipoTitulo, null, 'N'), // Campo 95: Tipo do título
            $this->formatarCampo($this->identificacao, null, 'C'), // Campo 96: Identificação
            $this->formatarCampo($this->icmsDesonerado, null, 'N'), // Campo 97: ICMS Desonerado
            $this->formatarCampo($this->ipiDevolucao, null, 'N'), // Campo 98: IPI Devolução
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        return !empty($this->inscricaoFornecedor) &&
            !empty($this->cfop) &&
            $this->numeroDocumento > 0;
    }

    // Getters e Setters básicos (adicionar conforme necessidade)
    public function getInscricaoFornecedor(): string
    {
        return $this->inscricaoFornecedor;
    }

    public function getNumeroDocumento(): int
    {
        return $this->numeroDocumento;
    }

    public function getSerie(): ?string
    {
        return $this->serie;
    }

    public function getDataEmissao(): \DateTime
    {
        return $this->dataEmissao;
    }

    public function getValorProdutos(): float
    {
        return $this->valorProdutos;
    }

    public function getCodigoAcumulador(): ?string
    {
        return $this->codigoAcumulador;
    }

    public function getCfop(): string
    {
        return $this->cfop;
    }
}
