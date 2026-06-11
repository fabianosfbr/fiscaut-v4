<?php

namespace App\Integrations\DominioSistemas\Services;

use App\Integrations\DominioSistemas\Dtos\ItemNfeDto;
use App\Integrations\DominioSistemas\Dtos\SegmentoDto;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\Tag;
use App\Services\Xml\XmlReaderService;
use Illuminate\Support\Collection;

/**
 * Orquestra o fluxo completo de geração do TXT Domínio
 * Equivalente ao Python: gerar_dominio.py -> processar_zip() + gerar_linhas()
 */
class OrquestradorService
{
    private ResolvedorCfopService $resolvedorCfop;

    private CalculadorIcmsService $calculadorIcms;

    /** @var array<string, string> mapa de CNPJ -> sequencial produto */
    private array $catalogoProdutos = [];

    /** @var array<string, int> contador sequencial por raiz CNPJ */
    private array $seqRaiz = [];

    /** @var array<string, true> controle de unicidade produto já gerado */
    private array $produtosGerados = [];

    /** @var array<string, true> unidades já geradas */
    private array $unidadesGeradas = [];

    // Mapa descrição de unidades
    private const UNIDADE_DESC = [
        'UN' => 'UNIDADE', 'PC' => 'PECA', 'KG' => 'QUILOGRAMA',
        'CX' => 'CAIXA', 'LT' => 'LITRO', 'MT' => 'METRO',
        'M2' => 'METRO QUADRADO', 'GL' => 'GALAO', 'SC' => 'SACO',
        'PR' => 'PAR', 'MWH' => 'MEGAWATT HORA', 'KWH' => 'KILOWATT HORA',
        'L' => 'LITRO',
    ];

    // Mapa finNFe -> situação documento (campo 41 do 1000)
    private const FIN_NFE_SITUACAO = ['1' => '00', '2' => '06', '3' => '08', '4' => '00'];

    // Mapa modFrete
    private const MOD_FRETE_MAP = ['0' => 'C', '1' => 'F', '2' => 'T', '3' => 'R', '4' => 'D', '9' => 'S'];

    private string $cnpjEmpresa;

    public function __construct(Issuer $issuer)
    {
        $this->resolvedorCfop = new ResolvedorCfopService($issuer);
        $this->calculadorIcms = new CalculadorIcmsService($this->resolvedorCfop);
        $this->cnpjEmpresa = $issuer->cnpj;
    }

    /**
     * Gera o conteúdo TXT completo a partir de uma coleção de NFs
     *
     * @param  Collection<int, NotaFiscalEletronica>  $notas
     * @return array{conteudo: string, avisos_ipi_bc: array, nfs: int, linhas: int, erros: int}
     */
    public function gerarTxt(Collection $notas): array
    {
        $linhas = [];
        $avisosIpiBc = [];
        $erros = 0;

        // Limpar caches
        $this->catalogoProdutos = [];
        $this->seqRaiz = [];
        $this->produtosGerados = [];
        $this->unidadesGeradas = [];

        // 0000 — cabeçalho
        $linhas[] = "|0000|{$this->cnpjEmpresa}|";

        // 0020 — fornecedores únicos
        $fornecedores = [];
        foreach ($notas as $notaFiscal) {
            $cnpj = $notaFiscal->emitente_cnpj;
            if (empty($cnpj) || isset($fornecedores[$cnpj])) {
                continue;
            }
            $fornecedores[$cnpj] = true;
            $linhas[] = $this->gerar0020($notaFiscal);
        }

        // Primeira passagem: construir catálogo de produtos
        foreach ($notas as $notaFiscal) {
            $this->construirCatalogo($notaFiscal);
        }

        // 0100 + 0150 — produtos e unidades
        foreach ($notas as $notaFiscal) {
            $this->gerarProdutos($notaFiscal, $linhas);
        }

        // NFs — 1000, 1010, 1020, 1030, 1200, 1500
        foreach ($notas as $notaFiscal) {
            try {
                $avisos = $this->gerarRegistrosNf($notaFiscal, $linhas);
                $avisosIpiBc = array_merge($avisosIpiBc, $avisos);
            } catch (\Exception $e) {
                $erros++;
                $linhas[] = "# ERRO NF {$notaFiscal->nNF}: {$e->getMessage()}";
            }
        }

        $conteudo = $this->montarConteudo($linhas);
        $totalLinhas = count($linhas);

        return [
            'conteudo' => $conteudo,
            'avisos_ipi_bc' => $avisosIpiBc,
            'nfs' => $notas->count(),
            'linhas' => $totalLinhas,
            'erros' => $erros,
        ];
    }

    // ─── Registro 0020 — Cadastro de Fornecedor ────────────────────
    // Leiaute: https://suporte.dominioatendimento.com/central/faces/solucao.html?codigo=672#0020
    // 33 campos no total
    private function gerar0020(NotaFiscalEletronica $notaFiscal): string
    {
        $cnpj = $notaFiscal->emitente_cnpj;
        $nome = $this->limpar(mb_substr($notaFiscal->emitente_razao_social ?? 'NAO INFORMADO', 0, 150));
        $apelido = $this->limpar(mb_substr($notaFiscal->emitente_razao_social ?? '', 0, 40));
        $ie = $notaFiscal->emitente_ie ?? '';
        $uf = $notaFiscal->enderEmit_UF ?? '';

        // Dados do XML: estrutura infNFe → emit → enderEmit
        $infNFe = $this->extrairInfNFe($notaFiscal);
        $emit = $infNFe['emit'] ?? [];
        $endEmit = $emit['enderEmit'] ?? [];

        // Regime tributário: CRT=1 => M (Simples/Microempresa), CRT=3 => N (Normal)
        $crt = $emit['CRT'] ?? '3';
        $regime = $crt === '1' ? 'M' : 'N';

        $endereco = $this->limpar($endEmit['xLgr'] ?? '');
        $numero = $endEmit['nro'] ?? '';
        $bairro = $this->limpar($endEmit['xBairro'] ?? '');
        $cMun = $endEmit['cMun'] ?? '';
        $cep = $endEmit['CEP'] ?? '';
        $fone = $endEmit['fone'] ?? '';
        $ddd = mb_substr(is_string($fone) ? $fone : '', 0, 2);
        $tel = mb_substr(is_string($fone) ? $fone : '', 2);

        // Array indexado (0-based) — campo N do leiaute = índice N-1
        $c = array_fill(0, 33, '');
        $c[0] = '0020';                          // 01 — Identificação (fixo: 0020)
        $c[1] = $cnpj;                            // 02 — Inscrição (CNPJ/CPF do fornecedor)
        $c[2] = $nome;                            // 03 — Razão Social (max 150)
        $c[3] = $apelido;                         // 04 — Apelido/Nome Reduzido (max 40)
        $c[4] = $endereco;                        // 05 — Endereço
        $c[5] = $numero;                          // 06 — Número
        $c[6] = '';                               // 07 — Complemento
        $c[7] = $bairro;                          // 08 — Bairro
        $c[8] = $cMun;                            // 09 — Código do Município
        $c[9] = $uf;                              // 10 — UF (EX para exterior)
        $c[10] = '';                               // 11 — Código do País (só para exterior)
        $c[11] = $cep;                             // 12 — CEP
        $c[12] = $ie;                              // 13 — Inscrição Estadual
        $c[13] = '';                               // 14 — Inscrição Municipal
        $c[14] = '';                               // 15 — Inscrição Suframa
        $c[15] = $ddd;                             // 16 — DDD (2 primeiros dígitos do fone)
        $c[16] = $tel;                             // 17 — Telefone (restante)
        $c[17] = '';                               // 18 — FAX
        $c[18] = '';                               // 19 — Data do Cadastro (dd/mm/aaaa)
        $c[19] = '';                               // 20 — Conta Contábil
        $c[20] = '';                               // 21 — Conta Contábil Cliente
        $c[21] = '';                               // 22 — Agropecuário (S/N)
        $c[22] = '';                               // 23 — Natureza Jurídica (1 a 8)
        $c[23] = $regime;                          // 24 — Regime de Apuração (N=Normal, M=Microempresa)
        $c[24] = 'N';                              // 25 — Contribuinte ICMS (S/N) — N=default
        $c[25] = '';                               // 26 — Alíquota ICMS
        $c[26] = '';                               // 27 — Categoria do Estabelecimento
        $c[27] = '';                               // 28 — Inscrição Estadual ST
        $c[28] = '';                               // 29 — Email
        $c[29] = '';                               // 30 — Interdependência (S/N)
        $c[30] = '';                               // 31 — Contribuinte da CPRB (S/N)
        $c[31] = '';                               // 32 — Processo adm./judicial (max 21)
        $c[32] = '';                               // 33 — Tipo Inscrição (1=CAEPF)

        return '|'.implode('|', $c).'|';
    }

    // ─── Catálogo de produtos ─────────────────────────────────────

    private function construirCatalogo(NotaFiscalEletronica $notaFiscal): void
    {
        $produtos = $notaFiscal->produtos;
        $raizCnpj = $this->raizCnpj($notaFiscal->emitente_cnpj);

        foreach ($produtos as $prod) {
            $cProd = $prod['cProd'] ?? '';
            $xProd = $prod['xProd'] ?? '';
            $uCom = strtoupper(trim($prod['uCom'] ?? 'UN'));
            $chave = "{$raizCnpj}|{$cProd}|{$xProd}|{$uCom}";

            if (! isset($this->catalogoProdutos[$chave])) {
                $this->seqRaiz[$raizCnpj] = ($this->seqRaiz[$raizCnpj] ?? 0) + 1;
                $this->catalogoProdutos[$chave] = sprintf("{$raizCnpj}_%03d", $this->seqRaiz[$raizCnpj]);
            }
        }
    }

    private function gerarProdutos(NotaFiscalEletronica $notaFiscal, array &$linhas): void
    {
        $produtos = $notaFiscal->produtos;
        $raizCnpj = $this->raizCnpj($notaFiscal->emitente_cnpj);

        foreach ($produtos as $prod) {
            $cProd = $prod['cProd'] ?? '';
            $xProd = $prod['xProd'] ?? '';
            $uCom = $prod['uCom'] ?? 'UN';
            $uComNorm = strtoupper(trim($uCom));
            $chave = "{$raizCnpj}|{$cProd}|{$xProd}|{$uComNorm}";

            if (isset($this->produtosGerados[$chave])) {
                continue;
            }
            $this->produtosGerados[$chave] = true;

            $cod = $this->catalogoProdutos[$chave];
            $desc = $this->limpar(mb_substr($xProd, 0, 60));
            $ncm = $prod['NCM'] ?? '';
            $vUn = $this->fmtDec((float) ($prod['vUnCom'] ?? 0), 3);

            // ─── Registro 0100 — Cadastro de Produto ─────────────────
            // Leiaute: https://suporte.dominioatendimento.com/central/faces/solucao.html?codigo=672#0100
            // 91 campos (90 + identificador)
            $c = array_fill(0, 91, '');
            $c[0] = '0100';                           // 01 — Identificação (fixo: 0100)
            $c[1] = $cod;                              // 02 — Código do Produto (max 14, ex: 14525282_001)
            $c[2] = $desc;                             // 03 — Descrição do Produto (max 60)
            $c[3] = '';                                // 04 — Código de Barras (EAN)
            $c[4] = $ncm;                              // 05 — NCM (8 dígitos)
            $c[5] = '';                                // 06 — EX IPI (TIPI)
            $c[6] = '';                                // 07 — Código CEST (regras específicas)
            $c[7] = '';                                // 08 — Código ANP (combustíveis)
            $c[8] = '1';                               // 09 — Grupo (1=padrão, revisão futura)
            $c[9] = $uCom;                             // 10 — Unidade de Medida (uCom real do XML)
            $c[10] = 'S';                               // 11 — Unid. Inv. Diferente (S=sim, permite reimportação)
            $c[11] = 'O';                               // 12 — Tipo do Produto (O=Outros)
            $c[12] = '';                                // 13 — Código do Serviço (ISSQN)
            $c[13] = '';                                // 14 — Código NBS
            $c[14] = '';                                // 15 — Natureza da Operação
            $c[15] = 'N';                               // 16 — ISSQN (N=não)
            $c[16] = '';                                // 17 — Valor Unitário Inv. (não usado)
            $c[17] = $vUn;                              // 18 — Valor Unitário (vUnCom, 3 casas)
            $c[18] = '';                                // 19 — Valor Unitário Compra (não usado)
            $c[19] = '';                                // 20 — Código Ajuste (Benefício Fiscal)
            $c[20] = '';                                // 21 — Código de Enquadramento
            $c[21] = '';                                // 22 — Código IPI (enquadramento legal)
            $c[22] = '';                                // 23 — Alíquota IPI Fixa
            $c[23] = 'M';                               // 24 — Periodicidade IPI (M=Mensal)
            // Campos 25-90 vazios
            $c[90] = $cod;                               // 91 — Identificador (sempre código interno, NUNCA EAN)

            $linhas[] = '|'.implode('|', $c).'|';

            // ─── Registro 0150 — Unidade de Medida ─────────────────
            // Leiaute: https://suporte.dominioatendimento.com/central/faces/solucao.html?codigo=672#0150
            // 3 campos
            if (! isset($this->unidadesGeradas[$uComNorm])) {
                $this->unidadesGeradas[$uComNorm] = true;
                $descUn = self::UNIDADE_DESC[$uComNorm] ?? $uComNorm;

                $u = array_fill(0, 3, '');
                $u[0] = '0150';                          // 01 — Identificação (fixo: 0150)
                $u[1] = $uCom;                            // 02 — Sigla da Unidade (ex: UN, KG, PC)
                $u[2] = $descUn;                          // 03 — Descrição da Unidade (ex: UNIDADE, QUILOGRAMA)

                $linhas[] = '|'.implode('|', $u).'|';
            }
        }
    }

    // ─── Registros da NF (1000, 1010, 1020, 1030, 1200, 1500) ────

    private function gerarRegistrosNf(NotaFiscalEletronica $notaFiscal, array &$linhas): array
    {
        $avisosIpiBc = [];
        $infNFe = $this->extrairInfNFe($notaFiscal);

        // Dados da NF
        $chave = $notaFiscal->chave ?? '';
        $nNF = $notaFiscal->nNF;
        $serie = $notaFiscal->serie;
        $dtEmi = $notaFiscal->data_emissao ? $notaFiscal->data_emissao->format('d/m/Y') : date('d/m/Y');
        $dtEnt = $notaFiscal->data_entrada ? $notaFiscal->data_entrada->format('d/m/Y') : $dtEmi;
        $ufEmit = $notaFiscal->enderEmit_UF ?? '';

        // CRT do emitente (lido do XML)
        $emit = $infNFe['emit'] ?? [];
        $crt = $emit['CRT'] ?? '3';
        $isSimples = $crt === '1';

        $tpNf = (int) ($notaFiscal->tpNf ?? 0);
        $modFrete = self::MOD_FRETE_MAP[$notaFiscal->modFrete ?? '0'] ?? 'C';

        // InfAdic
        $infCpl = $this->limpar($infNFe['infAdic']['infCpl'] ?? '');
        $infAdFisco = $this->limpar($infNFe['infAdic']['infAdFisco'] ?? '');
        $finNFe = $infNFe['ide']['finNFe'] ?? '1';
        $codSituacao = self::FIN_NFE_SITUACAO[$finNFe] ?? '00';

        // Tags da NF
        $tags = $notaFiscal->tagged ?? collect();
        if ($tags->isEmpty()) {
            return $avisosIpiBc;
        }

        // Rateio multiplas etiquetas: calcular percentual de cada tag
        // Ex: NF com tag1 de R$ 324,00 e tag2 de R$ 753,40
        //   total = 1077,40
        //   pct_tag1 = 324,00 / 1077,40 = 30,07%
        //   pct_tag2 = 753,40 / 1077,40 = 69,93%
        // Cada tag gera seus registros com valores proporcionais
        $temMultiplasTags = $tags->count() > 1;
        $totalValorTags = $temMultiplasTags ? (float) $tags->sum('value') : 1.0;

        $produtos = $notaFiscal->produtos;

        foreach ($tags as $tagged) {
            $tagId = $tagged->tag_id;
            $tagModel = $tagged->tag;
            if (! $tagModel) {
                continue;
            }

            // Percentual desta tag no rateio
            $pct = $temMultiplasTags ? ((float) ($tagged->value ?? 0)) / $totalValorTags : 1.0;

            $itensProcessados = [];

            // Determinar flags por tag
            $credIcms = ! $this->resolvedorCfop->isZeraIcms($tagId);
            $credIpi = ! $this->resolvedorCfop->isZeraIpi($tagId);
            $credPiscof = ! $isSimples; // SN não tem PIS/COFINS
            $debDifal = true; // TODO: derivar da tag

            foreach ($produtos as $prod) {
                $item = ItemNfeDto::fromArray($prod + ['is_simples' => $isSimples, 'cred_icms' => $credIcms, 'cred_piscof' => $credPiscof]);

                // Resolver CFOP de entrada
                $cfopEntrada = $this->resolvedorCfop->resolverCfop(
                    $tagId,
                    $item->cfopSaida,
                    $ufEmit,
                    $tpNf,
                    $notaFiscal->emitente_cnpj,
                );

                // Aplicar rateio proporcional se múltiplas tags
                $itemRateado = $item->withCfopAndFlags($cfopEntrada, $credIcms, $credPiscof, $pct);

                $itensProcessados[] = $itemRateado;
            }

            // ─── Correção IPI na BC ICMS ───────────────────────────
            // Quando o fornecedor destaca IPI e inclui na base do ICMS, a base
            // deve ser corrigida para vProd (sem IPI) e o ICMS recalculado.
            // Condição: cred_icms=true + não Simples + vBC ≈ vProd + vIPI
            // Aplica apenas em etiquetas que tomam crédito de ICMS
            // Equivalente ao Python: v22o, linhas 506-523
            if ($credIcms && ! $isSimples) {
                foreach ($itensProcessados as $itemIdx => $item) {
                    if ($item->ipiNaBc && $item->icmsVBC > 0) {
                        $vBCOrig = $item->icmsVBC;
                        $vICMSOrig = $item->icmsVICMS;

                        // Base corrigida = vProd (sem IPI)
                        $vBCCorr = $item->vProd;
                        $vICMSCorr = round($vBCCorr * $item->icmsPICMS / 100, 2);

                        $dadosCorrigidos = [
                            'nItem' => $item->nItem,
                            'cProd' => $item->cProd,
                            'xProd' => $item->xProd,
                            'NCM' => $item->NCM,
                            'uCom' => $item->uCom,
                            'qCom' => $item->qCom,
                            'vUnCom' => $item->vUnCom,
                            'CFOP' => $item->cfopSaida,
                            'cfop_entrada' => $cfopEntrada,
                            'cEAN' => $item->ean,
                            'vProd' => $item->vProd,
                            'vFrete' => $item->vFrete,
                            'vSeg' => $item->vSeg,
                            'vDesc' => $item->vDesc,
                            'vOutro' => $item->vOutro,
                            'CSOSN' => $item->icmsCsosn,
                            'is_simples' => $isSimples,
                            'cred_icms' => $credIcms,
                            'cred_piscof' => $credPiscof,
                            'impostos' => [
                                'vBC' => $vBCCorr,
                                'CST' => $item->icmsCst,
                                'pICMS' => $item->icmsPICMS,
                                'vICMS' => $vICMSCorr,
                                'vICMSSTRet' => $item->icmsVST,
                                'vIPI' => $item->icmsVIPI,
                                'pIPI' => $item->ipiPIPI,
                                'vBC_IPI' => $item->ipiVBC,
                                'vPIS' => $item->pisVPIS,
                                'pPIS' => $item->pisPPIS,
                                'vCOFINS' => $item->cofVCOFINS,
                                'pCOFINS' => $item->cofPCOFINS,
                            ],
                        ];

                        $itemCorrigido = ItemNfeDto::fromArray($dadosCorrigidos);
                        $itemCorrigido = $itemCorrigido->withCfopAndFlags($cfopEntrada, $credIcms, $credPiscof, $pct);
                        $itensProcessados[$itemIdx] = $itemCorrigido;

                        $msg = "NF {$nNF} item {$item->nItem} ({$this->limpar(mb_substr($item->xProd, 0, 30))}): "
                             .'IPI excluido da BC ICMS '
                             ."({$this->fmtDec($vBCOrig)}→{$this->fmtDec($vBCCorr)}, "
                             ."ICMS {$this->fmtDec($vICMSOrig)}→{$this->fmtDec($vICMSCorr)})";
                        $avisosIpiBc[] = $msg;
                    }
                }
            }

            // ─── CAT 14/2009 — Crédito ICMS-ST ──────────────────────
            // Portaria CAT 14/2009: adquirente pode se creditar do ICMS retido por ST
            // Condição: cfop_entrada 1401/2401 + regime normal (tag ICMS, não ICMSSN)
            //           + cred_icms=True
            // Base: vBCEfet do XML (preferencial) ou vProd como fallback
            // Alíq: pICMSEfet do XML (preferencial) ou 18% fixo SP como fallback
            // Equivalente ao Python: v22o, linhas 526-563
            foreach ($itensProcessados as $itemIdx => $item) {
                $cfopEntItem = $item->cfopEntrada;
                $ehSnItem = ! empty($item->icmsCsosn);

                if (in_array($cfopEntItem, ['1401', '2401']) && ! $ehSnItem && $credIcms) {
                    $vBCEfet = $item->icmsVBCEfet;
                    $pICMSEfet = $item->icmsPICMSEfet;
                    $vICMSEfet = $item->icmsVICMSEfet;

                    if ($vBCEfet > 0 && $vICMSEfet > 0) {
                        $vBCC14 = $vBCEfet;
                        $aliqC14 = $pICMSEfet;
                        $vICMSC14 = $vICMSEfet;
                        $fonte = "XML vBCEfet={$this->fmtDec($vBCEfet)} pICMSEfet={$this->fmtDec($aliqC14)}%";
                    } else {
                        $vBCC14 = $item->vProd;
                        $aliqC14 = 18.0;
                        $vICMSC14 = round($vBCC14 * 0.18, 2);
                        $fonte = 'fallback vProd x 18%';
                    }

                    // Construir dados corrigidos para o item
                    $dadosC14 = [
                        'nItem' => $item->nItem,
                        'cProd' => $item->cProd,
                        'xProd' => $item->xProd,
                        'NCM' => $item->NCM,
                        'uCom' => $item->uCom,
                        'qCom' => $item->qCom,
                        'vUnCom' => $item->vUnCom,
                        'CFOP' => $item->cfopSaida,
                        'cfop_entrada' => $cfopEntItem,
                        'cEAN' => $item->ean,
                        'vProd' => $item->vProd,
                        'vFrete' => $item->vFrete,
                        'vSeg' => $item->vSeg,
                        'vDesc' => $item->vDesc,
                        'vOutro' => $item->vOutro,
                        'CSOSN' => $item->icmsCsosn,
                        'is_simples' => $isSimples,
                        'cred_icms' => $credIcms,
                        'cred_piscof' => $credPiscof,
                        'impostos' => [
                            'vBC' => $vBCC14,
                            'CST' => '60',
                            'pICMS' => $aliqC14,
                            'vICMS' => $vICMSC14,
                            'vICMSSTRet' => $item->icmsVST,
                            'vIPI' => $item->icmsVIPI,
                            'pIPI' => $item->ipiPIPI,
                            'vBC_IPI' => $item->ipiVBC,
                            'vPIS' => $item->pisVPIS,
                            'pPIS' => $item->pisPPIS,
                            'vCOFINS' => $item->cofVCOFINS,
                            'pCOFINS' => $item->cofPCOFINS,
                        ],
                    ];

                    $itemC14 = ItemNfeDto::fromArray($dadosC14);
                    $itemC14 = $itemC14->withCfopAndFlags($cfopEntItem, $credIcms, $credPiscof, $pct);
                    $itensProcessados[$itemIdx] = $itemC14;

                    $msg = "[CAT14] NF {$nNF} item {$item->nItem} "
                         ."({$this->limpar(mb_substr($item->xProd, 0, 30))}): "
                         ."CFOP {$cfopEntItem} "
                         ."BC={$this->fmtDec($vBCC14)} aliq={$this->fmtDec($aliqC14)}% "
                         ."ICMS={$this->fmtDec($vICMSC14)} [{$fonte}]";
                    $avisosIpiBc[] = $msg;
                }
            }

            // Resolver acumulador
            $acumulador = $this->resolvedorCfop->resolverAcumulador($tagId, $cfopEntrada);

            // Segmentar por CFOP
            $segmentos = SegmentoDto::segmentar($itensProcessados, $acumulador);
            $nSegsTotal = count($segmentos);

            foreach ($segmentos as $seg) {
                // 1000
                $linhas[] = $this->gerar1000($seg, $notaFiscal, $codSituacao, $modFrete, $dtEmi, $dtEnt, $infAdFisco, $infCpl, $chave);

                // 1010 — infCpl
                if (! empty($infCpl)) {
                    $linhas[] = "|1010|{$seg->nSeg}|{$infCpl}|";
                }

                // 1020 ICMS
                foreach ($this->calculadorIcms->gerar1020Icms($seg, $isSimples, $credIcms, $ufEmit) as $l) {
                    $linhas[] = $l;
                }

                // 1020 IPI
                foreach ($this->calculadorIcms->gerar1020Ipi($seg, $isSimples, $credIpi) as $l) {
                    $linhas[] = $l;
                }

                // 1020 DIFAL
                foreach ($this->calculadorIcms->gerar1020Difal($seg, $isSimples, $debDifal) as $l) {
                    $linhas[] = $l;
                }

                // 1030 — Itens
                foreach ($seg->itens as $item) {
                    $linhas[] = $this->gerar1030($item, $seg, $notaFiscal, $dtEnt, $tagId, $credIcms, $isSimples);
                }

                // 1200 — Simples Nacional
                if ($isSimples && ! $credIcms && $seg->nSeg === 0) {
                    $linhas[] = '|1200|0,00|0,00|0,00|';
                }
            }

            // 1500 — Parcelas
            $parcelas = $notaFiscal->parcelas;
            foreach ($parcelas as $dup) {
                $dVenc = $dup['dVenc'] ?? '';
                $vDup = $dup['vDup'] ?? '0,00';
                $nDup = $dup['nDup'] ?? '';
                if (! empty($dVenc)) {
                    $dVencFmt = $this->fmtData($dVenc);
                    $linhas[] = "|1500|{$dVencFmt}|{$vDup}|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|{$nDup}|";
                }
            }
        }

        return $avisosIpiBc;
    }

    // ─── Registro 1000 ───────────────────────────────────────────
    // Leiaute: https://suporte.dominioatendimento.com/central/faces/solucao.html?codigo=672#1000

    private function gerar1000(SegmentoDto $seg, NotaFiscalEletronica $notaFiscal, string $codSituacao, string $modFrete, string $dtEmi, string $dtEnt, string $infAdFisco, string $infCpl, string $chave): string
    {
        $campos = array_fill(0, 98, '');
        $campos[0] = '1000';                              // 01 — Identificação (fixo: 1000)
        $campos[1] = '36';                                // 02 — Espécie (36=NF-e mod 55)
        $campos[2] = $notaFiscal->emitente_cnpj ?? '';    // 03 — CNPJ do fornecedor
        $campos[3] = '';                                  // 04 — Filial (quando aplicável)
        $campos[4] = (string) $seg->acumulador;           // 05 — Acumulador (da etiqueta)
        $campos[5] = $seg->cfop;                          // 06 — CFOP de entrada
        $campos[6] = (string) $seg->nSeg;                 // 07 — Segmento (0=único, 1,2,3...)
        $campos[7] = $notaFiscal->nNF ?? '';              // 08 — Número da NF
        $campos[8] = $notaFiscal->serie ?? '';            // 09 — Série
        $campos[9] = '';                                  // 10 — Sub-série
        $campos[10] = $dtEnt;                             // 11 — Data de Entrada (dd/mm/aaaa)
        $campos[11] = $dtEmi;                             // 12 — Data de Emissão (dd/mm/aaaa)
        $campos[12] = $this->fmtDec($seg->vContabil());   // 13 — Valor Contábil
        $campos[13] = '';                                 // 14 — Nº do Documento (quando diferente do nNF)
        $campos[14] = mb_substr($infAdFisco, 0, 250);     // 15 — Informações ao Fisco (infAdFisco)
        $campos[15] = $modFrete;                          // 16 — Modalidade do Frete (C/F/T/R/D/S)
        $campos[16] = 'T';                                // 17 — Emitente (T=Terceiros)
        $campos[17] = '';                                 // 18 — Data de Saída (para NF de saída)
        $campos[18] = '';                                 // 19 — CNPJ do transportador
        $campos[19] = '';                                 // 20 — Placa do veículo
        $campos[20] = '';                                 // 21 — UF da placa
        $campos[21] = '';                                 // 22 — RNTC (Registro Nacional Transportes)
        $campos[22] = '';                                 // 23 — Código do Município de origem
        $campos[23] = '';                                 // 24 — Código do Município de destino
        $campos[24] = '';                                 // 25 — Valor do ICMS Total
        $campos[25] = $this->fmtDec($seg->vFrete);        // 26 — Valor do Frete
        $campos[26] = $this->fmtDec($seg->vSeg);          // 27 — Valor do Seguro
        $campos[27] = $this->fmtDec($seg->vDesc);         // 28 — Valor do Desconto
        $campos[28] = $this->fmtDec($seg->vOutro);        // 29 — Outras Despesas
        $campos[29] = '';                                 // 30 — Valor do II
        $campos[30] = '';                                 // 31 — Valor do PIS
        $campos[31] = '';                                 // 32 — Valor do COFINS
        $campos[32] = '';                                 // 33 — Valor do IPI
        $campos[33] = '';                                 // 34 — Valor do ICMS ST
        $campos[34] = '';                                 // 35 — Valor do ICMS Desonerado
        $campos[35] = '';                                 // 36 — Valor do ICMS Diferido
        $campos[36] = '';                                 // 37 — Valor do ICMS de Saída
        $campos[37] = '';                                 // 38 — Valor do ICMS de Entrada
        $campos[38] = $this->fmtDec($seg->vProd);         // 39 — Valor dos Produtos
        $campos[39] = '';                                 // 40 — Valor do ISS
        $campos[40] = $codSituacao;                       // 41 — Situação do Documento (00=regular, 06=complementar, 08=regime especial)
        $campos[41] = '';                                 // 42 — Tipo do Documento
        $campos[42] = '';                                 // 43 — Data do Documento
        $campos[43] = '';                                 // 44 — Chave do Documento Referenciado
        $campos[44] = '';                                 // 45 — Número do Documento Referenciado
        $campos[45] = '';                                 // 46 — Série do Documento Referenciado
        $campos[46] = '';                                 // 47 — CFOP do Documento Referenciado
        $campos[47] = '';                                 // 48 — CNPJ do Documento Referenciado
        $campos[48] = '';                                 // 49 — Modelo do Documento Referenciado
        $campos[49] = '';                                 // 50 — Código do Município do Documento Referenciado
        $campos[50] = '';                                 // 51 — UF do Documento Referenciado
        $campos[51] = '';                                 // 52 — Inscrição Estadual do Documento Referenciado
        $campos[52] = '';                                 // 53 — Valor do ICMS Próprio
        $campos[53] = $chave;                             // 54 — Chave da NF-e (44 dígitos)
        $campos[54] = '';                                 // 55 — Número do Protocolo
        $campos[55] = '';                                 // 56 — Data do Protocolo
        $campos[56] = '';                                 // 57 — CNPJ do Consumidor Final
        $campos[57] = '';                                 // 58 — CPF do Consumidor Final
        $campos[58] = '';                                 // 59 — Nome do Consumidor Final
        $campos[59] = '';                                 // 60 — Endereço do Consumidor Final
        $campos[60] = '';                                 // 61 — Bairro do Consumidor Final
        $campos[61] = mb_substr($infCpl, 0, 250);         // 62 — Informações Complementares (infCpl)
        // Campos 63-89 vazios
        if ($seg->vIPI > 0) {
            $campos[89] = $this->fmtDec($seg->vIPI);      // 90 — Valor do IPI (apenas se > 0)
        }

        return '|'.implode('|', $campos).'|';
    }

    // ─── Registro 1030 ───────────────────────────────────────────

    private function gerar1030(ItemNfeDto $item, SegmentoDto $seg, NotaFiscalEletronica $notaFiscal, string $dtEnt, int $tagId, bool $credIcms, bool $isSimples): string
    {
        $cst = $item->getCstOuCsosn();
        $vContItem = $item->vContabil();
        $raizCnpj = $this->raizCnpj($notaFiscal->emitente_cnpj);
        $chaveProd = "{$raizCnpj}|{$item->cProd}|{$item->xProd}|{$item->uComNorm}";
        $codProd = $this->catalogoProdutos[$chaveProd] ?? '000000_000';

        // Campos ICMS no 1030 (espelham 1020)
        $temIcms1020 = $credIcms && ! $isSimples;
        if ($isSimples && $credIcms && $item->icmsVCredSN > 0) {
            $vlrIcms1030 = $item->icmsVCredSN;
            $bcIcms1030 = $item->vProd;
            $aliqIcms1030 = $item->icmsPCredSN;
        } elseif ($temIcms1020) {
            $vlrIcms1030 = $item->icmsVICMS;
            $bcIcms1030 = $item->icmsVBC;
            $aliqIcms1030 = $item->icmsPICMS;
        } else {
            $vlrIcms1030 = 0.0;
            $bcIcms1030 = 0.0;
            $aliqIcms1030 = 0.0;
        }

        // PIS/COFINS
        // Determinar base_credito_campo67 da tag
        $baseCredito67 = '';
        $pc = $this->calculadorIcms->calcularPiscofItem($item, $item->credPiscof, $baseCredito67);

        // CST IPI: 00=crédito, 49=sem crédito
        $credIpi = ! $this->resolvedorCfop->isZeraIpi($tagId);
        $cstIpi = ($credIpi && ! $isSimples && $item->icmsVIPI > 0) ? '00' : '49';

        // 111 campos
        $c = array_fill(0, 111, '');
        $c[0] = '1030';
        $c[1] = $codProd;
        $c[2] = $this->fmtQtd($item->qCom);
        $c[3] = $this->fmtDec($item->vProd);
        $c[4] = $this->fmtDec($item->icmsVIPI);
        $c[5] = $this->fmtDec($bcIcms1030);
        $c[6] = '1'; // tipo lançamento
        $c[7] = $dtEnt;
        $c[8] = '';
        $c[9] = $cst;
        $c[10] = $this->fmtDec($item->vProd);
        $c[11] = $this->fmtDec($item->vDesc);
        $c[12] = $this->fmtDec($bcIcms1030);
        $c[13] = $this->fmtDec($item->icmsVST);
        $c[14] = $this->fmtDec($aliqIcms1030);
        $c[15] = 'N';
        $c[16] = '0';
        $c[17] = $this->fmtDec($item->vFrete);
        $c[18] = $this->fmtDec($item->vSeg);
        $c[19] = $this->fmtDec($item->vOutro);
        $c[20] = '0,000';
        $c[21] = $this->fmtDec($vlrIcms1030);
        $c[22] = $this->fmtDec($item->icmsVST);
        $c[23] = '0,00';
        $c[24] = '0,00';
        $c[25] = '0,00';
        $c[26] = $this->fmtDec($item->vUnCom);
        $c[27] = '0,00';
        $c[28] = $cstIpi;
        $c[29] = $this->fmtDec($item->ipiPIPI);
        $c[30] = '0,00';
        $c[31] = '0,00';
        $c[32] = '0,00';
        $c[33] = $seg->cfop;
        $c[34] = '';
        $c[35] = $pc['aliq_pis'];
        $c[36] = $pc['vlr_pis'];
        $c[37] = $pc['aliq_cofins'];
        $c[38] = $pc['vlr_cofins'];
        $c[39] = $this->fmtDec($vContItem);
        $c[40] = $pc['cst_pis'];
        $c[41] = $pc['bc_pis'];
        $c[42] = $pc['cst_cofins'];
        $c[43] = $pc['bc_cofins'];
        $c[54] = '999'; // enquadramento IPI
        $c[55] = 'S'; // movimentação física
        $c[56] = $item->uCom; // unidade (NUNCA "S")
        $c[59] = $this->fmtDec($vContItem);
        $c[66] = $pc['base_credito']; // campo 67

        // IBS/CBS 104-111
        $c[103] = $item->ibsCClass;
        $c[104] = $this->fmtDec($item->ibsBC);
        $c[105] = $this->fmtDec($item->ibsAliq, 4);
        $c[106] = $this->fmtDec($item->ibsVal);
        $c[107] = $item->cbsCClass;
        $c[108] = $this->fmtDec($item->cbsBC);
        $c[109] = $this->fmtDec($item->cbsAliq, 4);
        $c[110] = $this->fmtDec($item->cbsVal);

        return '|'.implode('|', $c).'|';
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Extrai os dados do XML da NF, navegando pela estrutura nfeProc → NFe → infNFe
     */
    private function extrairInfNFe(NotaFiscalEletronica $notaFiscal): array
    {
        $raw = $notaFiscal->xml ?? '';
        if (empty($raw)) {
            return [];
        }

        $uncompressed = @gzuncompress($raw);
        if ($uncompressed === false) {
            $uncompressed = $raw;
        }

        try {
            $service = new XmlReaderService;
            $dados = $service->read($uncompressed);

            // Navega pela estrutura: nfeProc → NFe → infNFe
            return $dados['nfeProc']['NFe']['infNFe'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function montarConteudo(array $linhas): string
    {
        $conteudo = implode("\r\n", $linhas);

        return mb_convert_encoding($conteudo, 'Windows-1252', 'UTF-8');
    }

    private function limpar(string $t): string
    {
        $map = [
            'Ç' => 'C', 'ç' => 'c', 'Ã' => 'A', 'ã' => 'a',
            'Â' => 'A', 'â' => 'a', 'Á' => 'A', 'á' => 'a',
            'À' => 'A', 'à' => 'a', 'É' => 'E', 'é' => 'e',
            'Ê' => 'E', 'ê' => 'e', 'Í' => 'I', 'í' => 'i',
            'Ó' => 'O', 'ó' => 'o', 'Ô' => 'O', 'ô' => 'o',
            'Õ' => 'O', 'õ' => 'o', 'Ú' => 'U', 'ú' => 'u',
            'Ü' => 'U', 'ü' => 'u', 'Ñ' => 'N', 'ñ' => 'n',
            '|' => '-',
        ];

        return strtr($t, $map);
    }

    private function fmtDec(float $v, int $c = 2): string
    {
        return number_format($v, $c, ',', '');
    }

    private function fmtQtd(float $v): string
    {
        return number_format($v, 4, ',', '');
    }

    private function fmtData(string $data): string
    {
        $data = explode('T', $data)[0];
        if (str_contains($data, '-')) {
            $parts = explode('-', $data);
            if (count($parts) === 3) {
                return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
            }
        }

        // Se já está em dd/mm/aaaa ou outro formato
        return $data;
    }

    private function raizCnpj(string $cnpj): string
    {
        return mb_substr(preg_replace('/\D/', '', $cnpj) ?? '', 0, 8);
    }
}
