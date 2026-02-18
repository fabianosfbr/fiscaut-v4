<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\Issuer;
use App\Models\GeneralSetting;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Cache;
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
    private ?float $valorExclusaoDief = null;
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
    private ?int $classificacaoServicos = null;

    /**
     * @var float Fator de proporcionalidade baseado no valor da etiqueta
     */
    private float $fatorProporcionalidade = 1.0;

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
        Issuer $issuer,
        ?int $tagId = null
    ) {
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
        // Dados básicos da nota fiscal
        $this->codigoEspecie = '36'; // NF-e
        // Campo 4
        $this->codigoExclusaoDief = 0; // Não exclui

        $this->inscricaoFornecedor = $this->definirCnpjFornecedor($xmlData, $notaFiscal);
        $this->cfop = $this->obterCfopEquivalente($notaFiscal, $issuer, $tagId, $xmlData['emit']['enderEmit']['UF']); // Campo 6
        $this->numeroDocumento = (int)($notaFiscal->nNF ?? 0);
        $this->serie = $notaFiscal->serie ?? null;
        $this->dataEntrada = $this->converterParaDateTime($notaFiscal->data_entrada ?? now());
        $this->dataEmissao = $this->converterParaDateTime($notaFiscal->data_emissao ?? now());

        // Valores totais da nota
        $valorTotal = (float)($notaFiscal->vNfe ?? 0);
        $this->valorContabil = $valorTotal;
        $this->valorProdutos = (float)($xmlData['total']['vProd'] ?? 0);

        // Campo 5 - Código do acumulador (ID da CategoryTag)
        $this->codigoAcumulador = $this->obterAcumuladorEquivalente($notaFiscal, $issuer, $tagId, $this->cfop);

        // Campo 10
        $this->numeroDocumentoFinal = 0;


        // Campo 18
        $this->cfopExtendidoDetalhamento = 0;

        // Campo 19
        $this->codigoTransferenciaCredito = 0;

        // Campo 23
        $this->dataVistoTransfCreditoIcms = $this->converterParaDateTime($notaFiscal->data_emissao);

        // Campo 24
        $this->fatoGeradorCrf = 'E';

        // Campo 25
        $this->fatoGeradorIrrf = 'E';


        // Campo 82
        $this->dataEscrituracao = isset($notaFiscal->data_entrada) ? $this->converterParaDateTime($notaFiscal->data_entrada) : '';

        // Outros campos
        $this->municipioOrigem = $xmlData['enderEmit']['cMun'] ?? '';
        // Converte o enum para int ou usa o valor padrão
        $this->situacaoNota = $notaFiscal->status_nota instanceof \App\Enums\StatusNfeEnum
            ? (int)$notaFiscal->status_nota->value
            : ($notaFiscal->status_nota ?? 100);

        // Modalidade do frete
        $this->modalidadeFrete = $this->checkTipoFrete($notaFiscal->modFrete) ?? null;

        // Emitente da nota (P=Próprio, T=Terceiros)
        $this->emitenteNotaFiscal = 'P';

        // Valores proporcionais (campos 26, 27, 28, 29, 31, 39)
        $this->aplicarProporcionalidadeValores($xmlData, $notaFiscal);

        // Inscrições do fornecedor
        $this->inscricaoEstadualFornecedor = $xmlData['emit']['IE'] ?? null;
        $this->inscricaoMunicipalFornecedor = $xmlData['emit']['IM'] ?? null;

        // Chave da NF-e
        $this->chaveNotaFiscalEletronica = $notaFiscal->chave ?? null;

        // Informações complementares        
        $this->observacao = $xmlData['infAdic']['infAdFisco'] ?? str_replace('|', '-', $xmlData['infAdic']['infAdFisco'] ?? '');
        $this->informacaoComplementar = $xmlData['infAdic']['infCpl'] ?? str_replace('|', '-', $xmlData['infAdic']['infCpl'] ?? '');
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
        $cfopOriginal = $this->extrairPrimeiroCFOP($notaFiscal);

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
     * Define o CNPJ do fornecedor baseado no tipo de operação (nacional ou importação)
     *
     * @param array $xmlData
     * @param NotaFiscalEletronica $notaFiscal
     * @return string
     */
    private function definirCnpjFornecedor(array $xmlData, NotaFiscalEletronica $notaFiscal): string
    {
        // Verifica se é uma nota de importação pelo CFOP
        // CFOPs de importação começam com 3 (ex: 3101, 3201, 3202, 3205, 3206, 3207, 3208, 3209, 3211, 3251, 3551, 3667)
        $cfop = $this->extrairPrimeiroCFOP($notaFiscal);
        $isImportacao = strpos($cfop, '3') === 0;

        if ($isImportacao) {
            // Para importação, usa CNPJ genérico
            return '00000000000000';
        }

        // Para operação nacional, usa o CNPJ do emitente
        return $xmlData['emit']['CNPJ'] ?? $xmlData['emit']['CPF'] ?? $notaFiscal->emitente_cnpj ?? '';
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
        $this->valorFrete = $this->aplicarProporcionalidade((float)($notaFiscal->vFrete ?? 0));
        $this->valorSeguro = $this->aplicarProporcionalidade((float)($notaFiscal->vSeg ?? 0));
        $this->valorDespesas = $this->aplicarProporcionalidade((float)($notaFiscal->vOutro ?? 0));
        $this->valorPis = $this->aplicarProporcionalidade((float)($notaFiscal->vPIS ?? 0));
        $this->valorCofins = $this->aplicarProporcionalidade((float)($notaFiscal->vCOFINS ?? 0));
        $this->valorProdutos = $this->aplicarProporcionalidade((float)($xmlData['total']['vProd'] ?? 0));
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
     * Extrai o primeiro CFOP dos produtos da nota fiscal
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @return string
     */
    private function extrairPrimeiroCFOP(NotaFiscalEletronica $notaFiscal): string
    {
        $produtos = $notaFiscal->produtos ?? [];
        if (!empty($produtos) && isset($produtos[0]['CFOP'])) {
            return (string)$produtos[0]['CFOP'];
        }
        return '5405'; // Valor padrão
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
            $this->formatarCampo($this->classificacaoServicos, null, 'N'), // Campo 92: Classificação de Serviços Prestados
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
