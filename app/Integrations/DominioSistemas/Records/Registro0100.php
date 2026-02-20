<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\NotaFiscalEletronica;

/**
 * Registro 0100 - Cadastro de Produtos
 * Utilizado para importar ou atualizar o cadastro de produtos.
 *
 * Campos:
 * 1 - Identificação do registro (fixo: 0100)
 * 2 - Código do produto (máximo de 14 caracteres)
 * 3 - Descrição do produto
 * 4 - Código NBM
 * 5 - Código NCM
 * 6 - Código NCM Exterior
 * 7 - Código de barras
 * 8 - Código do imposto de importação
 * 9 - Código do grupo de produtos
 * 10 - Unidade de medida
 * 11 - Unidade de medida inventaria diferente da comercializada (S/N)
 * 12 - Tipo do produto (A=Arma de fogo, M=Medicamentos, V=Veículos novos, O=Outros)
 * 13 - Tipo da arma de fogo (0=Uso permitido, 1=Uso restrito)
 * 14 - Descrição da arma de fogo
 * 15 - Tipo de medicamento (0=Similar, 1=Genérico, 2=Ético ou marca)
 * 16 - Serviço tributado pelo ISSQN (S/N)
 * 17 - Código do chassi do veículo
 * 18 - Valor unitário
 * 19 - Quantidade inicial em estoque
 * 20 - Valor inicial em estoque
 * 21 - Código da situação tributária do ICMS
 * 22 - Alíquota do ICMS
 * 23 - Alíquota do IPI
 * 24 - Periodicidade do IPI (D=Decendial, M=Mensal)
 * 25 - Observação
 * 26 - Exporta produto para DNF (S/N)
 * 27 - Ex TIPI
 * 28 - DNF – Código da espécie do produto
 * 29 - DNF – Unidade de medida padrão
 * 30 - DNF- Fator de conversão
 * 31 - DNF – Código do produto
 * 32 - DNF – Capacidade Volumétrica
 * 33 - SE/DIC – Código EAN
 * 34 - SE/DIC – Código do produto relevante
 * 35 - SCANC – Gerar para o SCANC (S/N)
 * 36 - SCANC – Código do produto no SCANC
 * 37 - SCANC – Contém gasolina A (S/N)
 * 38 - SCANC – Tipo de produto
 * 39 - GRF – CTB – Gera para o GRF – CTB (S/N)
 * 40 - GRF – CTB – Código do produto
 * 41 - DIEF - Unidade (UN=Unidade, KG=Quilograma, LT=Litro, MT=Metro Linear, M2=Metro quadrado, M3=Metro cúbico, KW=Quilowatt hora, PR=Par)
 * 42 - DIEF – Tipo de produto/serviço (1=Mercadoria, 2= Serviço com incidência de ICMS, 3=Serviço sem incidência do ICMS)
 * 43 - 88ST - Informa o registro 88ST do Sintegra (S/N)
 * 44 - 88ST - Código do produto na tabela Sefaz
 * 45 - GO – Informações complementares do IPM da DPI
 * 46 - GO – Código do produto/serviço do IPM da DPI
 * 47 - GO - Produto relacionado (S/N)
 * 48 - AM - Cesta básica (S/N)
 * 49 - AM - Código do produto na DAM
 * 50 - RS - Produto incluído no campo substituição tributária
 * 51 - RS - Data de início da substituição tributária (dd/mm/aaaa)
 * 52 - RS - Produto com preço tabelado (S/N)
 * 53 - RS - Valor unitário da substituição tributária
 * 54 - RS - MVA da substituição tributária
 * 55 - RS - Grupo da substituição tributária
 * 56 - PR - Equipamento de ECF (S/N)
 * 57 - MS - Possui incentivo fiscal (S/N)
 * 58 - DF - Produto sujeito ao regime especial (1=Sim, 0=Não)
 * 59 - DF - Item padrão regime especial
 * 60 - PE - Tipo do produto (1=Mercadoria, 2=Matéria Prima, 3=Produto Intermediário, 4=Materiais de embalagens, 5=Produtos manufaturados, 6=Produtos em fabricação)
 * 61 - SP – Controla ressarcimento Cat 17/99 (S/N)
 * 62 - SP - Data do saldo inicial controle Cat 17/99 (dd/mm/aaaa)
 * 63 - SP - Valor unitários controle Cat 17/99
 * 64 - SP - Quantidade controle Cat 17/99
 * 65 - SP – Valor final controle Cat 17/99
 * 66 - SPED - Gênero
 * 67 - SPED – Código do Serviço
 * 68 - SPED – Tipo do item (0=Mercadoria, 1=Matéria Prima, 2=Produto Intermediário, 3=Produto em Fabricação, 4=Produto Acabado, 5=Embalagem, 6=Subproduto, 7=Material de Uso e Consumo, 8=Ativo Imobilizado, 9=Serviços, 10=Outros Insumos, 99=Outros)
 * 69 - SPED – Classificação
 * 70 - SPED – Conta Contábil estoque – Em seu poder
 * 71 - SPED – Conta Contábil estoque – Em poder de terceiros
 * 72 - SPED – Conta Contábil estoque – De terceiros em seu poder
 * 73 - SPED – Tipo de receita (0=Receita própria, 1=Receita de terceiros)
 * 74 - SPED - Energia elétrica / Gás canalizado
 * 75 - Data do cadastro (dd/mm/aaaa)
 * 76 - Produto escriturado no LMC (S/N)
 * 77 - Código do combustível conforme tabela do DF
 * 78 - Código do combustível conforme tabela da ANP
 * 79 - Produto relacionado nos incisos do Art. 8º da MP nº 540/2011 (S/N)
 * 80 - Permitir informar a descrição complementar no lancto. das notas
 * 81 - Código de atividade – INSS Folha
 * 82 - DACON – Tipo do Produto
 * 83 - DACON - Crédito Presumido Atividade Agroindustriais (1=Insumos de origem animal, 2=Insumos de origem vegetal, 0=sem informação)
 * 84 - Desconsiderar
 * 85 - SPED – Conta Contábil estoque - Em processo
 * 86 - SPED – Conta Contábil estoque - Histórico em processo
 * 87 - SPED – Conta Contábil estoque - Acabado
 * 88 - SPED – Conta Contábil estoque - Histórico acabado
 * 89 - Código CEST
 * 90 - Registro de Exportação (RE)
 * 91 - Identificador (máximo de 60 caracteres)
 */
class Registro0100 extends RegistroBase
{
    private string $codigoProduto;
    private string $descricaoProduto;
    private ?string $codigoNbm = null;
    private ?string $codigoNcm = null;
    private ?string $codigoNcmExterior = null;
    private ?string $codigoBarras = null;
    private ?int $codigoImpostoImportacao = null;
    private ?int $codigoGrupoProdutos = null;
    private ?string $unidadeMedida = null;
    private ?string $unidadeMedidaDiferente = null; // S/N
    private ?string $tipoProduto = null; // A, M, V, O
    private ?int $tipoArmaFogo = null; // 0, 1
    private ?string $descricaoArmaFogo = null;
    private ?int $tipoMedicamento = null; // 0, 1, 2
    private ?string $servicoIssqn = null; // S/N
    private ?string $codigoChassiVeiculo = null;
    private ?float $valorUnitario = null;
    private ?float $quantidadeEstoqueInicial = null;
    private ?float $valorEstoqueInicial = null;
    private ?int $codigoSitTributariaIcms = null;
    private ?float $aliquotaIcms = null;
    private ?float $aliquotaIpi = null;
    private ?string $periodicidadeIpi = null; // D, M
    private ?string $observacao = null;
    private ?string $exportaDnf = null; // S/N
    private ?string $exTipi = null;
    private ?int $dnfCodigoEspecieProduto = null;
    private ?int $dnfUnidadeMedidaPadrao = null;
    private ?float $dnfFatorConversao = null;
    private ?int $dnfCodigoProduto = null;
    private ?int $dnfCapacidadeVolumetrica = null;
    private ?string $seDicCodigoEan = null;
    private ?int $seDicCodigoProdutoRelevante = null;
    private ?string $scancGera = null; // S/N
    private ?int $scancCodigoProduto = null;
    private ?string $scancContemGasolinaA = null; // S/N
    private ?string $scancTipoProduto = null;
    private ?string $grfCtbGera = null; // S/N
    private ?int $grfCtbCodigoProduto = null;
    private ?string $diefUnidade = null; // UN, KG, LT, MT, M2, M3, KW, PR
    private ?int $diefTipoProdutoServico = null; // 1, 2, 3
    private ?string $sintegra88St = null; // S/N
    private ?int $sintegra88StCodigoProduto = null;
    private ?string $goInfoIpmDpi = null;
    private ?int $goCodigoProdutoServicoIpmDpi = null;
    private ?string $goProdutoRelacionado = null; // S/N
    private ?string $amCestaBasica = null; // S/N
    private ?int $amCodigoProdutoDam = null;
    private ?string $rsProdutoSubstTributaria = null;
    private ?string $rsDataInicioSubstTributaria = null; // dd/mm/aaaa
    private ?string $rsProdutoPrecoTabelado = null; // S/N
    private ?float $rsValorUnitarioSubstTributaria = null;
    private ?float $rsMvaSubstTributaria = null;
    private ?string $rsGrupoSubstTributaria = null;
    private ?string $prEquipamentoEcf = null; // S/N
    private ?string $msPossuiIncentivoFiscal = null; // S/N
    private ?string $dfProdutoRegimeEspecial = null; // 1, 0
    private ?int $dfItemPadraoRegimeEspecial = null;
    private ?int $peTipoProduto = null; // 1, 2, 3, 4, 5, 6
    private ?string $spControlaRessarcimentoCat1799 = null; // S/N
    private ?string $spDataSaldoInicialCat1799 = null; // dd/mm/aaaa
    private ?float $spValorUnitariosCat1799 = null;
    private ?float $spQuantidadeCat1799 = null;
    private ?float $spValorFinalCat1799 = null;
    private ?int $spedGenero = null;
    private ?int $spedCodigoServico = null;
    private ?int $spedTipoItem = null; // 0-9, 99
    private ?int $spedClassificacao = null;
    private ?int $spedContaContabilEstoqueEmSeuPoder = null;
    private ?int $spedContaContabilEstoqueEmPoderTerceiros = null;
    private ?int $spedContaContabilEstoqueTerceirosEmSeuPoder = null;
    private ?string $spedTipoReceita = null; // 0, 1
    private ?int $spedEnergiaGasCanalizado = null;
    private ?string $dataCadastro = null; // dd/mm/aaaa
    private ?string $produtoEscrituradoLmc = null; // S/N
    private ?string $codigoCombustivelDf = null;
    private ?string $codigoCombustivelAnp = null;
    private ?string $produtoRelacionadoMp5402011 = null; // S/N
    private ?string $descricaoComplementarLancamentoNotas = null;
    private ?string $codigoAtividadeInssFolha = null;
    private ?string $daconTipoProduto = null;
    private ?string $daconCreditoPresumidoAgroindustriais = null; // 1, 2, 0
    private ?int $desconsiderar = null;
    private ?int $spedContaContabilEstoqueEmProcesso = null;
    private ?int $spedContaContabilEstoqueHistoricoProcesso = null;
    private ?int $spedContaContabilEstoqueAcabado = null;
    private ?int $spedContaContabilEstoqueHistoricoAcabado = null;
    private ?int $codigoCest = null;
    private ?int $registroExportacaoRe = null;
    private ?string $identificador = null;

    /**
     * Cache estático para armazenar os CategoryTags já consultados
     * Key: category_id
     * Value: CategoryTag model instance
     *
     * @var array
     */
    private static array $categoryTagCache = [];



    public function __construct(
        NotaFiscalEletronica $notaFiscal,
        array $produto,
        ?string $issuerCnpj = null
    ) {
        // Extrai os dados do produto do array fornecido
        $this->codigoProduto = $this->obterIdentificador($notaFiscal, $produto, $issuerCnpj);
        $this->descricaoProduto = $produto['xProd'] ?? '';

        // Preenche os campos adicionais com base nos dados do produto
        if (isset($produto['NCM'])) {
            $this->codigoNcm = $produto['NCM'];
        }
        if (isset($produto['cEAN'])) {
            $this->codigoBarras = $produto['cEAN'];
        }
        if (isset($produto['uCom'])) {
            $this->unidadeMedida = $produto['uCom'];
        }
        if (isset($produto['vUnCom'])) {
            $this->valorUnitario = (float)$produto['vUnCom'];
        }
        if (isset($produto['vProd'])) {
            $this->valorEstoqueInicial = (float)$produto['vProd'];
        }
        if (isset($produto['qCom'])) {
            $this->quantidadeEstoqueInicial = (float)$produto['qCom'];
        }
        if (isset($produto['CFOP'])) {
            $this->codigoSitTributariaIcms = (int)$produto['CFOP'];
        }

        // Campo 9: Código do grupo de produtos
        // Obtém o código do grupo (CategoryTag) a partir da etiqueta aplicada à nota fiscal

        $this->codigoGrupoProdutos = $this->obterGrupoDoCategoryTag($notaFiscal);

        // Campo 70: SPED – Conta Contábil estoque – Em seu poder
        // Obtém a conta contábil do grupo (CategoryTag) a partir da etiqueta aplicada à nota fiscal
        $this->spedContaContabilEstoqueEmSeuPoder = $this->obterContaContabilDoGrupo($notaFiscal);

        // Campo 89: Código CEST
        // Obtém o CEST a partir do NCM
        $this->codigoCest = self::getCest($this->codigoNcm);

        // Campo 91: Identificador
        // Se a nota fiscal é entrada própria, usa o código do produto
        // Caso contrário, usa o external_id do ProdutoFornecedor
        $this->identificador = $this->obterIdentificador($notaFiscal, $produto, $issuerCnpj);
    }

    /**
     * Obtém a conta contábil do grupo de produtos a partir da etiqueta aplicada à nota fiscal
     * Utiliza cache estático para evitar consultas repetidas ao banco de dados
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @return int|null
     */
    private function obterContaContabilDoGrupo(NotaFiscalEletronica $notaFiscal): ?int
    {
        // Obtém a primeira tag através do relacionamento tagged (HasTags trait)
        $tagged = $notaFiscal->tagged->first();

        if (!$tagged || !$tagged->tag || !$tagged->tag->category_id) {
            return null;
        }

        $categoryId = $tagged->tag->category_id;

        // Obtém o CategoryTag do cache (ou busca se não existir)
        $categoryTag = $this->getCategoryTagFromCache($categoryId);

        // Retorna a conta contábil se existir
        return $categoryTag?->conta_contabil ? (int)$categoryTag->conta_contabil : null;
    }

    /**
     * Obtém o código do grupo do CategoryTag a partir da etiqueta aplicada à nota fiscal
     * Utiliza o mesmo cache estático de CategoryTags
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @return int|null
     */
    private function obterGrupoDoCategoryTag(NotaFiscalEletronica $notaFiscal): ?int
    {
        // Obtém a primeira tag através do relacionamento tagged (HasTags trait)
        $tagged = $notaFiscal->tagged->first();

        if (!$tagged || !$tagged->tag || !$tagged->tag->category_id) {
            return null;
        }

        $categoryId = $tagged->tag->category_id;

        // Obtém o CategoryTag do cache (ou busca se não existir)
        $categoryTag = $this->getCategoryTagFromCache($categoryId);

        // Retorna o grupo se existir
        return $categoryTag?->grupo ? (int)$categoryTag->grupo : null;
    }

    /**
     * Obtém o CategoryTag do cache ou faz a consulta ao banco de dados
     *
     * @param int $categoryId
     * @return \App\Models\CategoryTag|null
     */
    private function getCategoryTagFromCache(int $categoryId): ?\App\Models\CategoryTag
    {
        // Verifica se já está em cache
        if (!isset(self::$categoryTagCache[$categoryId])) {
            // Busca o CategoryTag e armazena em cache
            self::$categoryTagCache[$categoryId] = \App\Models\CategoryTag::find($categoryId);
        }

        return self::$categoryTagCache[$categoryId];
    }

    



    public function getTipoRegistro(): string
    {
        return '0100';
    }

    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação do registro
            $this->formatarCampo($this->codigoProduto, 14, 'C'), // Campo 2: Código do produto (máximo de 14 caracteres)
            $this->formatarCampo($this->descricaoProduto, null, 'C'), // Campo 3: Descrição do produto
            $this->formatarCampo($this->codigoNbm, null, 'C'), // Campo 4: Código NBM
            $this->formatarCampo($this->codigoNcm, null, 'C'), // Campo 5: Código NCM
            $this->formatarCampo($this->codigoNcmExterior, null, 'N'), // Campo 6: Código NCM Exterior
            $this->formatarCampo($this->codigoBarras, null, 'C'), // Campo 7: Código de barras
            $this->formatarCampo($this->codigoImpostoImportacao, null, 'N'), // Campo 8: Código do imposto de importação
            $this->formatarCampo($this->codigoGrupoProdutos, null, 'N'), // Campo 9: Código do grupo de produtos
            $this->formatarCampo($this->unidadeMedida, null, 'C'), // Campo 10: Unidade de medida
            $this->formatarCampo($this->unidadeMedidaDiferente, null, 'C'), // Campo 11: Unidade de medida inventaria diferente da comercializada (S/N)
            $this->formatarCampo($this->tipoProduto, null, 'C'), // Campo 12: Tipo do produto (A, M, V, O)
            $this->formatarCampo($this->tipoArmaFogo, null, 'N'), // Campo 13: Tipo da arma de fogo (0, 1)
            $this->formatarCampo($this->descricaoArmaFogo, null, 'C'), // Campo 14: Descrição da arma de fogo
            $this->formatarCampo($this->tipoMedicamento, null, 'N'), // Campo 15: Tipo de medicamento (0, 1, 2)
            $this->formatarCampo($this->servicoIssqn, null, 'C'), // Campo 16: Serviço tributado pelo ISSQN (S/N)
            $this->formatarCampo($this->codigoChassiVeiculo, null, 'C'), // Campo 17: Código do chassi do veículo
            $this->formatarCampo($this->valorUnitario, null, 'D'), // Campo 18: Valor unitário
            $this->formatarCampo($this->quantidadeEstoqueInicial, null, 'D'), // Campo 19: Quantidade inicial em estoque
            $this->formatarCampo($this->valorEstoqueInicial, null, 'D'), // Campo 20: Valor inicial em estoque
            $this->formatarCampo($this->codigoSitTributariaIcms, null, 'N'), // Campo 21: Código da situação tributária do ICMS
            $this->formatarCampo($this->aliquotaIcms, null, 'D'), // Campo 22: Alíquota do ICMS
            $this->formatarCampo($this->aliquotaIpi, null, 'D'), // Campo 23: Alíquota do IPI
            $this->formatarCampo($this->periodicidadeIpi, null, 'C'), // Campo 24: Periodicidade do IPI (D, M)
            $this->formatarCampo($this->observacao, null, 'C'), // Campo 25: Observação
            $this->formatarCampo($this->exportaDnf, null, 'C'), // Campo 26: Exporta produto para DNF (S/N)
            $this->formatarCampo($this->exTipi, null, 'C'), // Campo 27: Ex TIPI
            $this->formatarCampo($this->dnfCodigoEspecieProduto, null, 'N'), // Campo 28: DNF – Código da espécie do produto
            $this->formatarCampo($this->dnfUnidadeMedidaPadrao, null, 'N'), // Campo 29: DNF – Unidade de medida padrão
            $this->formatarCampo($this->dnfFatorConversao, null, 'D'), // Campo 30: DNF- Fator de conversão
            $this->formatarCampo($this->dnfCodigoProduto, null, 'N'), // Campo 31: DNF – Código do produto
            $this->formatarCampo($this->dnfCapacidadeVolumetrica, null, 'N'), // Campo 32: DNF – Capacidade Volumétrica
            $this->formatarCampo($this->seDicCodigoEan, null, 'C'), // Campo 33: SE/DIC – Código EAN
            $this->formatarCampo($this->seDicCodigoProdutoRelevante, null, 'N'), // Campo 34: SE/DIC – Código do produto relevante
            $this->formatarCampo($this->scancGera, null, 'C'), // Campo 35: SCANC – Gerar para o SCANC (S/N)
            $this->formatarCampo($this->scancCodigoProduto, null, 'N'), // Campo 36: SCANC – Código do produto no SCANC
            $this->formatarCampo($this->scancContemGasolinaA, null, 'C'), // Campo 37: SCANC – Contém gasolina A (S/N)
            $this->formatarCampo($this->scancTipoProduto, null, 'C'), // Campo 38: SCANC – Tipo de produto
            $this->formatarCampo($this->grfCtbGera, null, 'C'), // Campo 39: GRF – CTB – Gera para o GRF – CTB (S/N)
            $this->formatarCampo($this->grfCtbCodigoProduto, null, 'N'), // Campo 40: GRF – CTB – Código do produto
            $this->formatarCampo($this->diefUnidade, null, 'C'), // Campo 41: DIEF - Unidade (UN, KG, LT, MT, M2, M3, KW, PR)
            $this->formatarCampo($this->diefTipoProdutoServico, null, 'N'), // Campo 42: DIEF – Tipo de produto/serviço (1, 2, 3)
            $this->formatarCampo($this->sintegra88St, null, 'C'), // Campo 43: 88ST - Informa o registro 88ST do Sintegra (S/N)
            $this->formatarCampo($this->sintegra88StCodigoProduto, null, 'N'), // Campo 44: 88ST - Código do produto na tabela Sefaz
            $this->formatarCampo($this->goInfoIpmDpi, null, 'C'), // Campo 45: GO – Informações complementares do IPM da DPI
            $this->formatarCampo($this->goCodigoProdutoServicoIpmDpi, null, 'N'), // Campo 46: GO – Código do produto/serviço do IPM da DPI
            $this->formatarCampo($this->goProdutoRelacionado, null, 'C'), // Campo 47: GO - Produto relacionado (S/N)
            $this->formatarCampo($this->amCestaBasica, null, 'C'), // Campo 48: AM - Cesta básica (S/N)
            $this->formatarCampo($this->amCodigoProdutoDam, null, 'N'), // Campo 49: AM - Código do produto na DAM
            $this->formatarCampo($this->rsProdutoSubstTributaria, null, 'C'), // Campo 50: RS - Produto incluído no campo substituição tributária
            $this->formatarCampo($this->rsDataInicioSubstTributaria, null, 'X'), // Campo 51: RS - Data de início da substituição tributária (dd/mm/aaaa)
            $this->formatarCampo($this->rsProdutoPrecoTabelado, null, 'C'), // Campo 52: RS - Produto com preço tabelado (S/N)
            $this->formatarCampo($this->rsValorUnitarioSubstTributaria, null, 'D'), // Campo 53: RS - Valor unitário da substituição tributária
            $this->formatarCampo($this->rsMvaSubstTributaria, null, 'D'), // Campo 54: RS - MVA da substituição tributária
            $this->formatarCampo($this->rsGrupoSubstTributaria, null, 'C'), // Campo 55: RS - Grupo da substituição tributária
            $this->formatarCampo($this->prEquipamentoEcf, null, 'C'), // Campo 56: PR - Equipamento de ECF (S/N)
            $this->formatarCampo($this->msPossuiIncentivoFiscal, null, 'C'), // Campo 57: MS - Possui incentivo fiscal (S/N)
            $this->formatarCampo($this->dfProdutoRegimeEspecial, null, 'C'), // Campo 58: DF - Produto sujeito ao regime especial (1, 0)
            $this->formatarCampo($this->dfItemPadraoRegimeEspecial, null, 'N'), // Campo 59: DF - Item padrão regime especial
            $this->formatarCampo($this->peTipoProduto, null, 'N'), // Campo 60: PE - Tipo do produto (1, 2, 3, 4, 5, 6)
            $this->formatarCampo($this->spControlaRessarcimentoCat1799, null, 'C'), // Campo 61: SP – Controla ressarcimento Cat 17/99 (S/N)
            $this->formatarCampo($this->spDataSaldoInicialCat1799, null, 'X'), // Campo 62: SP - Data do saldo inicial controle Cat 17/99 (dd/mm/aaaa)
            $this->formatarCampo($this->spValorUnitariosCat1799, null, 'D'), // Campo 63: SP - Valor unitários controle Cat 17/99
            $this->formatarCampo($this->spQuantidadeCat1799, null, 'D'), // Campo 64: SP - Quantidade controle Cat 17/99
            $this->formatarCampo($this->spValorFinalCat1799, null, 'D'), // Campo 65: SP – Valor final controle Cat 17/99
            $this->formatarCampo($this->spedGenero, null, 'N'), // Campo 66: SPED - Gênero
            $this->formatarCampo($this->spedCodigoServico, null, 'N'), // Campo 67: SPED – Código do Serviço
            $this->formatarCampo($this->spedTipoItem, null, 'N'), // Campo 68: SPED – Tipo do item (0-9, 99)
            $this->formatarCampo($this->spedClassificacao, null, 'N'), // Campo 69: SPED – Classificação
            $this->formatarCampo($this->spedContaContabilEstoqueEmSeuPoder, null, 'N'), // Campo 70: SPED – Conta Contábil estoque – Em seu poder
            $this->formatarCampo($this->spedContaContabilEstoqueEmPoderTerceiros, null, 'N'), // Campo 71: SPED – Conta Contábil estoque – Em poder de terceiros
            $this->formatarCampo($this->spedContaContabilEstoqueTerceirosEmSeuPoder, null, 'N'), // Campo 72: SPED – Conta Contábil estoque – De terceiros em seu poder
            $this->formatarCampo($this->spedTipoReceita, null, 'C'), // Campo 73: SPED – Tipo de receita (0, 1)
            $this->formatarCampo($this->spedEnergiaGasCanalizado, null, 'N'), // Campo 74: SPED - Energia elétrica / Gás canalizado
            $this->formatarCampo($this->dataCadastro, null, 'X'), // Campo 75: Data do cadastro (dd/mm/aaaa)
            $this->formatarCampo($this->produtoEscrituradoLmc, null, 'C'), // Campo 76: Produto escriturado no LMC (S/N)
            $this->formatarCampo($this->codigoCombustivelDf, null, 'C'), // Campo 77: Código do combustível conforme tabela do DF
            $this->formatarCampo($this->codigoCombustivelAnp, null, 'C'), // Campo 78: Código do combustível conforme tabela da ANP
            $this->formatarCampo($this->produtoRelacionadoMp5402011, null, 'C'), // Campo 79: Produto relacionado nos incisos do Art. 8º da MP nº 540/2011 (S/N)
            $this->formatarCampo($this->descricaoComplementarLancamentoNotas, null, 'C'), // Campo 80: Permitir informar a descrição complementar no lancto. das notas
            $this->formatarCampo($this->codigoAtividadeInssFolha, null, 'C'), // Campo 81: Código de atividade – INSS Folha
            $this->formatarCampo($this->daconTipoProduto, null, 'C'), // Campo 82: DACON – Tipo do Produto
            $this->formatarCampo($this->daconCreditoPresumidoAgroindustriais, null, 'C'), // Campo 83: DACON - Crédito Presumido Atividade Agroindustriais (1, 2, 0)
            $this->formatarCampo($this->desconsiderar, null, 'N'), // Campo 84: Desconsiderar
            $this->formatarCampo($this->spedContaContabilEstoqueEmProcesso, null, 'N'), // Campo 85: SPED – Conta Contábil estoque - Em processo
            $this->formatarCampo($this->spedContaContabilEstoqueHistoricoProcesso, null, 'N'), // Campo 86: SPED – Conta Contábil estoque - Histórico em processo
            $this->formatarCampo($this->spedContaContabilEstoqueAcabado, null, 'N'), // Campo 87: SPED – Conta Contábil estoque - Acabado
            $this->formatarCampo($this->spedContaContabilEstoqueHistoricoAcabado, null, 'N'), // Campo 88: SPED – Conta Contábil estoque - Histórico acabado
            $this->formatarCampo($this->codigoCest, null, 'N'), // Campo 89: Código CEST
            $this->formatarCampo($this->registroExportacaoRe, null, 'N'), // Campo 90: Registro de Exportação (RE)
            $this->formatarCampo($this->identificador, 60, 'C'), // Campo 91: Identificador (máximo de 60 caracteres)
        ];

        return $this->montarLinha($campos);
    }

    public function isValid(): bool
    {
        // Validação específica para o Registro 0100
        return !empty($this->codigoProduto) && !empty($this->descricaoProduto);
    }

    // Getters
    public function getCodigoProduto(): string
    {
        return $this->codigoProduto;
    }
    public function getDescricaoProduto(): string
    {
        return $this->descricaoProduto;
    }
    public function getCodigoNbm(): ?string
    {
        return $this->codigoNbm;
    }
    public function getCodigoNcm(): ?string
    {
        return $this->codigoNcm;
    }
    public function getCodigoNcmExterior(): ?string
    {
        return $this->codigoNcmExterior;
    }
    public function getCodigoBarras(): ?string
    {
        return $this->codigoBarras;
    }
    public function getCodigoImpostoImportacao(): ?int
    {
        return $this->codigoImpostoImportacao;
    }
    public function getCodigoGrupoProdutos(): ?int
    {
        return $this->codigoGrupoProdutos;
    }
    public function getUnidadeMedida(): ?string
    {
        return $this->unidadeMedida;
    }
    public function getUnidadeMedidaDiferente(): ?string
    {
        return $this->unidadeMedidaDiferente;
    }
    public function getTipoProduto(): ?string
    {
        return $this->tipoProduto;
    }
    public function getTipoArmaFogo(): ?int
    {
        return $this->tipoArmaFogo;
    }
    public function getDescricaoArmaFogo(): ?string
    {
        return $this->descricaoArmaFogo;
    }
    public function getTipoMedicamento(): ?int
    {
        return $this->tipoMedicamento;
    }
    public function getServicoIssqn(): ?string
    {
        return $this->servicoIssqn;
    }
    public function getCodigoChassiVeiculo(): ?string
    {
        return $this->codigoChassiVeiculo;
    }
    public function getValorUnitario(): ?float
    {
        return $this->valorUnitario;
    }
    public function getQuantidadeEstoqueInicial(): ?float
    {
        return $this->quantidadeEstoqueInicial;
    }
    public function getValorEstoqueInicial(): ?float
    {
        return $this->valorEstoqueInicial;
    }
    public function getCodigoSitTributariaIcms(): ?int
    {
        return $this->codigoSitTributariaIcms;
    }
    public function getAliquotaIcms(): ?float
    {
        return $this->aliquotaIcms;
    }
    public function getAliquotaIpi(): ?float
    {
        return $this->aliquotaIpi;
    }
    public function getPeriodicidadeIpi(): ?string
    {
        return $this->periodicidadeIpi;
    }
    public function getObservacao(): ?string
    {
        return $this->observacao;
    }
    public function getExportaDnf(): ?string
    {
        return $this->exportaDnf;
    }
    public function getExTipi(): ?string
    {
        return $this->exTipi;
    }
    public function getDnfCodigoEspecieProduto(): ?int
    {
        return $this->dnfCodigoEspecieProduto;
    }
    public function getDnfUnidadeMedidaPadrao(): ?int
    {
        return $this->dnfUnidadeMedidaPadrao;
    }
    public function getDnfFatorConversao(): ?float
    {
        return $this->dnfFatorConversao;
    }
    public function getDnfCodigoProduto(): ?int
    {
        return $this->dnfCodigoProduto;
    }
    public function getDnfCapacidadeVolumetrica(): ?int
    {
        return $this->dnfCapacidadeVolumetrica;
    }
    public function getSeDicCodigoEan(): ?string
    {
        return $this->seDicCodigoEan;
    }
    public function getSeDicCodigoProdutoRelevante(): ?int
    {
        return $this->seDicCodigoProdutoRelevante;
    }
    public function getScancGera(): ?string
    {
        return $this->scancGera;
    }
    public function getScancCodigoProduto(): ?int
    {
        return $this->scancCodigoProduto;
    }
    public function getScancContemGasolinaA(): ?string
    {
        return $this->scancContemGasolinaA;
    }
    public function getScancTipoProduto(): ?string
    {
        return $this->scancTipoProduto;
    }
    public function getGrfCtbGera(): ?string
    {
        return $this->grfCtbGera;
    }
    public function getGrfCtbCodigoProduto(): ?int
    {
        return $this->grfCtbCodigoProduto;
    }
    public function getDieFUnidade(): ?string
    {
        return $this->diefUnidade;
    }
    public function getDieFTipoProdutoServico(): ?int
    {
        return $this->diefTipoProdutoServico;
    }
    public function getSintegra88St(): ?string
    {
        return $this->sintegra88St;
    }
    public function getSintegra88StCodigoProduto(): ?int
    {
        return $this->sintegra88StCodigoProduto;
    }
    public function getGoInfoIpmDpi(): ?string
    {
        return $this->goInfoIpmDpi;
    }
    public function getGoCodigoProdutoServicoIpmDpi(): ?int
    {
        return $this->goCodigoProdutoServicoIpmDpi;
    }
    public function getGoProdutoRelacionado(): ?string
    {
        return $this->goProdutoRelacionado;
    }
    public function getAmCestaBasica(): ?string
    {
        return $this->amCestaBasica;
    }
    public function getAmCodigoProdutoDam(): ?int
    {
        return $this->amCodigoProdutoDam;
    }
    public function getRsProdutoSubstTributaria(): ?string
    {
        return $this->rsProdutoSubstTributaria;
    }
    public function getRsDataInicioSubstTributaria(): ?string
    {
        return $this->rsDataInicioSubstTributaria;
    }
    public function getRsProdutoPrecoTabelado(): ?string
    {
        return $this->rsProdutoPrecoTabelado;
    }
    public function getRsValorUnitarioSubstTributaria(): ?float
    {
        return $this->rsValorUnitarioSubstTributaria;
    }
    public function getRsMvaSubstTributaria(): ?float
    {
        return $this->rsMvaSubstTributaria;
    }
    public function getRsGrupoSubstTributaria(): ?string
    {
        return $this->rsGrupoSubstTributaria;
    }
    public function getPrEquipamentoEcf(): ?string
    {
        return $this->prEquipamentoEcf;
    }
    public function getMsPossuiIncentivoFiscal(): ?string
    {
        return $this->msPossuiIncentivoFiscal;
    }
    public function getDfProdutoRegimeEspecial(): ?string
    {
        return $this->dfProdutoRegimeEspecial;
    }
    public function getDfItemPadraoRegimeEspecial(): ?int
    {
        return $this->dfItemPadraoRegimeEspecial;
    }
    public function getPeTipoProduto(): ?int
    {
        return $this->peTipoProduto;
    }
    public function getSpControlaRessarcimentoCat1799(): ?string
    {
        return $this->spControlaRessarcimentoCat1799;
    }
    public function getSpDataSaldoInicialCat1799(): ?string
    {
        return $this->spDataSaldoInicialCat1799;
    }
    public function getSpValorUnitariosCat1799(): ?float
    {
        return $this->spValorUnitariosCat1799;
    }
    public function getSpQuantidadeCat1799(): ?float
    {
        return $this->spQuantidadeCat1799;
    }
    public function getSpValorFinalCat1799(): ?float
    {
        return $this->spValorFinalCat1799;
    }
    public function getSpedGenero(): ?int
    {
        return $this->spedGenero;
    }
    public function getSpedCodigoServico(): ?int
    {
        return $this->spedCodigoServico;
    }
    public function getSpedTipoItem(): ?int
    {
        return $this->spedTipoItem;
    }
    public function getSpedClassificacao(): ?int
    {
        return $this->spedClassificacao;
    }
    public function getSpedContaContabilEstoqueEmSeuPoder(): ?int
    {
        return $this->spedContaContabilEstoqueEmSeuPoder;
    }
    public function getSpedContaContabilEstoqueEmPoderTerceiros(): ?int
    {
        return $this->spedContaContabilEstoqueEmPoderTerceiros;
    }
    public function getSpedContaContabilEstoqueTerceirosEmSeuPoder(): ?int
    {
        return $this->spedContaContabilEstoqueTerceirosEmSeuPoder;
    }
    public function getSpedTipoReceita(): ?string
    {
        return $this->spedTipoReceita;
    }
    public function getSpedEnergiaGasCanalizado(): ?int
    {
        return $this->spedEnergiaGasCanalizado;
    }
    public function getDataCadastro(): ?string
    {
        return $this->dataCadastro;
    }
    public function getProdutoEscrituradoLmc(): ?string
    {
        return $this->produtoEscrituradoLmc;
    }
    public function getCodigoCombustivelDf(): ?string
    {
        return $this->codigoCombustivelDf;
    }
    public function getCodigoCombustivelAnp(): ?string
    {
        return $this->codigoCombustivelAnp;
    }
    public function getProdutoRelacionadoMp5402011(): ?string
    {
        return $this->produtoRelacionadoMp5402011;
    }
    public function getDescricaoComplementarLancamentoNotas(): ?string
    {
        return $this->descricaoComplementarLancamentoNotas;
    }
    public function getCodigoAtividadeInssFolha(): ?string
    {
        return $this->codigoAtividadeInssFolha;
    }
    public function getDaconTipoProduto(): ?string
    {
        return $this->daconTipoProduto;
    }
    public function getDaconCreditoPresumidoAgroindustriais(): ?string
    {
        return $this->daconCreditoPresumidoAgroindustriais;
    }
    public function getDesconsiderar(): ?int
    {
        return $this->desconsiderar;
    }
    public function getSpedContaContabilEstoqueEmProcesso(): ?int
    {
        return $this->spedContaContabilEstoqueEmProcesso;
    }
    public function getSpedContaContabilEstoqueHistoricoProcesso(): ?int
    {
        return $this->spedContaContabilEstoqueHistoricoProcesso;
    }
    public function getSpedContaContabilEstoqueAcabado(): ?int
    {
        return $this->spedContaContabilEstoqueAcabado;
    }
    public function getSpedContaContabilEstoqueHistoricoAcabado(): ?int
    {
        return $this->spedContaContabilEstoqueHistoricoAcabado;
    }
    public function getCodigoCest(): ?int
    {
        return $this->codigoCest;
    }
    public function getRegistroExportacaoRe(): ?int
    {
        return $this->registroExportacaoRe;
    }
    public function getIdentificador(): ?string
    {
        return $this->identificador;
    }

    // Setters
    public function setCodigoProduto(string $codigoProduto): void
    {
        $this->codigoProduto = $codigoProduto;
    }
    public function setDescricaoProduto(string $descricaoProduto): void
    {
        $this->descricaoProduto = $descricaoProduto;
    }
    public function setCodigoNbm(?string $codigoNbm): void
    {
        $this->codigoNbm = $codigoNbm;
    }
    public function setCodigoNcm(?string $codigoNcm): void
    {
        $this->codigoNcm = $codigoNcm;
    }
    public function setCodigoNcmExterior(?string $codigoNcmExterior): void
    {
        $this->codigoNcmExterior = $codigoNcmExterior;
    }
    public function setCodigoBarras(?string $codigoBarras): void
    {
        $this->codigoBarras = $codigoBarras;
    }
    public function setCodigoImpostoImportacao(?int $codigoImpostoImportacao): void
    {
        $this->codigoImpostoImportacao = $codigoImpostoImportacao;
    }
    public function setCodigoGrupoProdutos(?int $codigoGrupoProdutos): void
    {
        $this->codigoGrupoProdutos = $codigoGrupoProdutos;
    }
    public function setUnidadeMedida(?string $unidadeMedida): void
    {
        $this->unidadeMedida = $unidadeMedida;
    }
    public function setUnidadeMedidaDiferente(?string $unidadeMedidaDiferente): void
    {
        $this->unidadeMedidaDiferente = $unidadeMedidaDiferente;
    }
    public function setTipoProduto(?string $tipoProduto): void
    {
        $this->tipoProduto = $tipoProduto;
    }
    public function setTipoArmaFogo(?int $tipoArmaFogo): void
    {
        $this->tipoArmaFogo = $tipoArmaFogo;
    }
    public function setDescricaoArmaFogo(?string $descricaoArmaFogo): void
    {
        $this->descricaoArmaFogo = $descricaoArmaFogo;
    }
    public function setTipoMedicamento(?int $tipoMedicamento): void
    {
        $this->tipoMedicamento = $tipoMedicamento;
    }
    public function setServicoIssqn(?string $servicoIssqn): void
    {
        $this->servicoIssqn = $servicoIssqn;
    }
    public function setCodigoChassiVeiculo(?string $codigoChassiVeiculo): void
    {
        $this->codigoChassiVeiculo = $codigoChassiVeiculo;
    }
    public function setValorUnitario(?float $valorUnitario): void
    {
        $this->valorUnitario = $valorUnitario;
    }
    public function setQuantidadeEstoqueInicial(?float $quantidadeEstoqueInicial): void
    {
        $this->quantidadeEstoqueInicial = $quantidadeEstoqueInicial;
    }
    public function setValorEstoqueInicial(?float $valorEstoqueInicial): void
    {
        $this->valorEstoqueInicial = $valorEstoqueInicial;
    }
    public function setCodigoSitTributariaIcms(?int $codigoSitTributariaIcms): void
    {
        $this->codigoSitTributariaIcms = $codigoSitTributariaIcms;
    }
    public function setAliquotaIcms(?float $aliquotaIcms): void
    {
        $this->aliquotaIcms = $aliquotaIcms;
    }
    public function setAliquotaIpi(?float $aliquotaIpi): void
    {
        $this->aliquotaIpi = $aliquotaIpi;
    }
    public function setPeriodicidadeIpi(?string $periodicidadeIpi): void
    {
        $this->periodicidadeIpi = $periodicidadeIpi;
    }
    public function setObservacao(?string $observacao): void
    {
        $this->observacao = $observacao;
    }
    public function setExportaDnf(?string $exportaDnf): void
    {
        $this->exportaDnf = $exportaDnf;
    }
    public function setExTipi(?string $exTipi): void
    {
        $this->exTipi = $exTipi;
    }
    public function setDnfCodigoEspecieProduto(?int $dnfCodigoEspecieProduto): void
    {
        $this->dnfCodigoEspecieProduto = $dnfCodigoEspecieProduto;
    }
    public function setDnfUnidadeMedidaPadrao(?int $dnfUnidadeMedidaPadrao): void
    {
        $this->dnfUnidadeMedidaPadrao = $dnfUnidadeMedidaPadrao;
    }
    public function setDnfFatorConversao(?float $dnfFatorConversao): void
    {
        $this->dnfFatorConversao = $dnfFatorConversao;
    }
    public function setDnfCodigoProduto(?int $dnfCodigoProduto): void
    {
        $this->dnfCodigoProduto = $dnfCodigoProduto;
    }
    public function setDnfCapacidadeVolumetrica(?int $dnfCapacidadeVolumetrica): void
    {
        $this->dnfCapacidadeVolumetrica = $dnfCapacidadeVolumetrica;
    }
    public function setSeDicCodigoEan(?string $seDicCodigoEan): void
    {
        $this->seDicCodigoEan = $seDicCodigoEan;
    }
    public function setSeDicCodigoProdutoRelevante(?int $seDicCodigoProdutoRelevante): void
    {
        $this->seDicCodigoProdutoRelevante = $seDicCodigoProdutoRelevante;
    }
    public function setScancGera(?string $scancGera): void
    {
        $this->scancGera = $scancGera;
    }
    public function setScancCodigoProduto(?int $scancCodigoProduto): void
    {
        $this->scancCodigoProduto = $scancCodigoProduto;
    }
    public function setScancContemGasolinaA(?string $scancContemGasolinaA): void
    {
        $this->scancContemGasolinaA = $scancContemGasolinaA;
    }
    public function setScancTipoProduto(?string $scancTipoProduto): void
    {
        $this->scancTipoProduto = $scancTipoProduto;
    }
    public function setGrfCtbGera(?string $grfCtbGera): void
    {
        $this->grfCtbGera = $grfCtbGera;
    }
    public function setGrfCtbCodigoProduto(?int $grfCtbCodigoProduto): void
    {
        $this->grfCtbCodigoProduto = $grfCtbCodigoProduto;
    }
    public function setDieFUnidade(?string $diefUnidade): void
    {
        $this->diefUnidade = $diefUnidade;
    }
    public function setDieFTipoProdutoServico(?int $diefTipoProdutoServico): void
    {
        $this->diefTipoProdutoServico = $diefTipoProdutoServico;
    }
    public function setSintegra88St(?string $sintegra88St): void
    {
        $this->sintegra88St = $sintegra88St;
    }
    public function setSintegra88StCodigoProduto(?int $sintegra88StCodigoProduto): void
    {
        $this->sintegra88StCodigoProduto = $sintegra88StCodigoProduto;
    }
    public function setGoInfoIpmDpi(?string $goInfoIpmDpi): void
    {
        $this->goInfoIpmDpi = $goInfoIpmDpi;
    }
    public function setGoCodigoProdutoServicoIpmDpi(?int $goCodigoProdutoServicoIpmDpi): void
    {
        $this->goCodigoProdutoServicoIpmDpi = $goCodigoProdutoServicoIpmDpi;
    }
    public function setGoProdutoRelacionado(?string $goProdutoRelacionado): void
    {
        $this->goProdutoRelacionado = $goProdutoRelacionado;
    }
    public function setAmCestaBasica(?string $amCestaBasica): void
    {
        $this->amCestaBasica = $amCestaBasica;
    }
    public function setAmCodigoProdutoDam(?int $amCodigoProdutoDam): void
    {
        $this->amCodigoProdutoDam = $amCodigoProdutoDam;
    }
    public function setRsProdutoSubstTributaria(?string $rsProdutoSubstTributaria): void
    {
        $this->rsProdutoSubstTributaria = $rsProdutoSubstTributaria;
    }
    public function setRsDataInicioSubstTributaria(?string $rsDataInicioSubstTributaria): void
    {
        $this->rsDataInicioSubstTributaria = $rsDataInicioSubstTributaria;
    }
    public function setRsProdutoPrecoTabelado(?string $rsProdutoPrecoTabelado): void
    {
        $this->rsProdutoPrecoTabelado = $rsProdutoPrecoTabelado;
    }
    public function setRsValorUnitarioSubstTributaria(?float $rsValorUnitarioSubstTributaria): void
    {
        $this->rsValorUnitarioSubstTributaria = $rsValorUnitarioSubstTributaria;
    }
    public function setRsMvaSubstTributaria(?float $rsMvaSubstTributaria): void
    {
        $this->rsMvaSubstTributaria = $rsMvaSubstTributaria;
    }
    public function setRsGrupoSubstTributaria(?string $rsGrupoSubstTributaria): void
    {
        $this->rsGrupoSubstTributaria = $rsGrupoSubstTributaria;
    }
    public function setPrEquipamentoEcf(?string $prEquipamentoEcf): void
    {
        $this->prEquipamentoEcf = $prEquipamentoEcf;
    }
    public function setMsPossuiIncentivoFiscal(?string $msPossuiIncentivoFiscal): void
    {
        $this->msPossuiIncentivoFiscal = $msPossuiIncentivoFiscal;
    }
    public function setDfProdutoRegimeEspecial(?string $dfProdutoRegimeEspecial): void
    {
        $this->dfProdutoRegimeEspecial = $dfProdutoRegimeEspecial;
    }
    public function setDfItemPadraoRegimeEspecial(?int $dfItemPadraoRegimeEspecial): void
    {
        $this->dfItemPadraoRegimeEspecial = $dfItemPadraoRegimeEspecial;
    }
    public function setPeTipoProduto(?int $peTipoProduto): void
    {
        $this->peTipoProduto = $peTipoProduto;
    }
    public function setSpControlaRessarcimentoCat1799(?string $spControlaRessarcimentoCat1799): void
    {
        $this->spControlaRessarcimentoCat1799 = $spControlaRessarcimentoCat1799;
    }
    public function setSpDataSaldoInicialCat1799(?string $spDataSaldoInicialCat1799): void
    {
        $this->spDataSaldoInicialCat1799 = $spDataSaldoInicialCat1799;
    }
    public function setSpValorUnitariosCat1799(?float $spValorUnitariosCat1799): void
    {
        $this->spValorUnitariosCat1799 = $spValorUnitariosCat1799;
    }
    public function setSpQuantidadeCat1799(?float $spQuantidadeCat1799): void
    {
        $this->spQuantidadeCat1799 = $spQuantidadeCat1799;
    }
    public function setSpValorFinalCat1799(?float $spValorFinalCat1799): void
    {
        $this->spValorFinalCat1799 = $spValorFinalCat1799;
    }
    public function setSpedGenero(?int $spedGenero): void
    {
        $this->spedGenero = $spedGenero;
    }
    public function setSpedCodigoServico(?int $spedCodigoServico): void
    {
        $this->spedCodigoServico = $spedCodigoServico;
    }
    public function setSpedTipoItem(?int $spedTipoItem): void
    {
        $this->spedTipoItem = $spedTipoItem;
    }
    public function setSpedClassificacao(?int $spedClassificacao): void
    {
        $this->spedClassificacao = $spedClassificacao;
    }
    public function setSpedContaContabilEstoqueEmSeuPoder(?int $spedContaContabilEstoqueEmSeuPoder): void
    {
        $this->spedContaContabilEstoqueEmSeuPoder = $spedContaContabilEstoqueEmSeuPoder;
    }
    public function setSpedContaContabilEstoqueEmPoderTerceiros(?int $spedContaContabilEstoqueEmPoderTerceiros): void
    {
        $this->spedContaContabilEstoqueEmPoderTerceiros = $spedContaContabilEstoqueEmPoderTerceiros;
    }
    public function setSpedContaContabilEstoqueTerceirosEmSeuPoder(?int $spedContaContabilEstoqueTerceirosEmSeuPoder): void
    {
        $this->spedContaContabilEstoqueTerceirosEmSeuPoder = $spedContaContabilEstoqueTerceirosEmSeuPoder;
    }
    public function setSpedTipoReceita(?string $spedTipoReceita): void
    {
        $this->spedTipoReceita = $spedTipoReceita;
    }
    public function setSpedEnergiaGasCanalizado(?int $spedEnergiaGasCanalizado): void
    {
        $this->spedEnergiaGasCanalizado = $spedEnergiaGasCanalizado;
    }
    public function setDataCadastro(?string $dataCadastro): void
    {
        $this->dataCadastro = $dataCadastro;
    }
    public function setProdutoEscrituradoLmc(?string $produtoEscrituradoLmc): void
    {
        $this->produtoEscrituradoLmc = $produtoEscrituradoLmc;
    }
    public function setCodigoCombustivelDf(?string $codigoCombustivelDf): void
    {
        $this->codigoCombustivelDf = $codigoCombustivelDf;
    }
    public function setCodigoCombustivelAnp(?string $codigoCombustivelAnp): void
    {
        $this->codigoCombustivelAnp = $codigoCombustivelAnp;
    }
    public function setProdutoRelacionadoMp5402011(?string $produtoRelacionadoMp5402011): void
    {
        $this->produtoRelacionadoMp5402011 = $produtoRelacionadoMp5402011;
    }
    public function setDescricaoComplementarLancamentoNotas(?string $descricaoComplementarLancamentoNotas): void
    {
        $this->descricaoComplementarLancamentoNotas = $descricaoComplementarLancamentoNotas;
    }
    public function setCodigoAtividadeInssFolha(?string $codigoAtividadeInssFolha): void
    {
        $this->codigoAtividadeInssFolha = $codigoAtividadeInssFolha;
    }
    public function setDaconTipoProduto(?string $daconTipoProduto): void
    {
        $this->daconTipoProduto = $daconTipoProduto;
    }
    public function setDaconCreditoPresumidoAgroindustriais(?string $daconCreditoPresumidoAgroindustriais): void
    {
        $this->daconCreditoPresumidoAgroindustriais = $daconCreditoPresumidoAgroindustriais;
    }
    public function setDesconsiderar(?int $desconsiderar): void
    {
        $this->desconsiderar = $desconsiderar;
    }
    public function setSpedContaContabilEstoqueEmProcesso(?int $spedContaContabilEstoqueEmProcesso): void
    {
        $this->spedContaContabilEstoqueEmProcesso = $spedContaContabilEstoqueEmProcesso;
    }
    public function setSpedContaContabilEstoqueHistoricoProcesso(?int $spedContaContabilEstoqueHistoricoProcesso): void
    {
        $this->spedContaContabilEstoqueHistoricoProcesso = $spedContaContabilEstoqueHistoricoProcesso;
    }
    public function setSpedContaContabilEstoqueAcabado(?int $spedContaContabilEstoqueAcabado): void
    {
        $this->spedContaContabilEstoqueAcabado = $spedContaContabilEstoqueAcabado;
    }
    public function setSpedContaContabilEstoqueHistoricoAcabado(?int $spedContaContabilEstoqueHistoricoAcabado): void
    {
        $this->spedContaContabilEstoqueHistoricoAcabado = $spedContaContabilEstoqueHistoricoAcabado;
    }
    public function setCodigoCest(?int $codigoCest): void
    {
        $this->codigoCest = $codigoCest;
    }
    public function setRegistroExportacaoRe(?int $registroExportacaoRe): void
    {
        $this->registroExportacaoRe = $registroExportacaoRe;
    }
    public function setIdentificador(?string $identificador): void
    {
        $this->identificador = $identificador;
    }
}
