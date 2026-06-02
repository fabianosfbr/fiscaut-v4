<?php

namespace App\Integrations\DominioSistemas\Services;

use App\Integrations\DominioSistemas\Dtos\ItemNfeDto;
use App\Integrations\DominioSistemas\Dtos\SegmentoDto;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\Tag;
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

    public function __construct(Issuer $issuer)
    {
        $this->resolvedorCfop = new ResolvedorCfopService($issuer);
        $this->calculadorIcms = new CalculadorIcmsService($this->resolvedorCfop);
    }

    /**
     * Gera o conteúdo TXT completo a partir de uma coleção de NFs
     * @param Collection<int, NotaFiscalEletronica> $notas
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
        $linhas[] = "|0000|" . sanitize($this->issuer->cnpj) . "|";

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

    // ─── Registro 0020 ─────────────────────────────────────────────

    private function gerar0020(NotaFiscalEletronica $notaFiscal): string
    {
        $cnpj = $notaFiscal->emitente_cnpj;
        $nome = $this->limpar(mb_substr($notaFiscal->emitente_razao_social ?? 'NAO INFORMADO', 0, 150));
        $apelido = $this->limpar(mb_substr($notaFiscal->emitente_razao_social ?? '', 0, 40));
        $ie = $notaFiscal->emitente_ie ?? '';
        $uf = $notaFiscal->enderEmit_UF ?? '';

        // Regime tributário: CRT=1 => M (Simples), CRT=3 => N (Normal)
        $regime = ($notaFiscal->emitente_crt ?? '3') === '1' ? 'M' : 'N';

        // Dados de endereço do XML
        $xmlData = $this->extrairXmlDados($notaFiscal);
        $endEmit = $xmlData['enderEmit'] ?? [];
        $endereco = $this->limpar($endEmit['xLgr'] ?? '');
        $numero = $endEmit['nro'] ?? '';
        $bairro = $this->limpar($endEmit['xBairro'] ?? '');
        $cMun = $endEmit['cMun'] ?? '';
        $cep = $endEmit['CEP'] ?? '';
        $fone = $endEmit['fone'] ?? '';
        $ddd = mb_substr($fone, 0, 2);
        $tel = mb_substr($fone, 2);

        return "|0020|{$cnpj}|{$nome}|{$apelido}|{$endereco}|{$numero}||{$bairro}|{$cMun}|{$uf}||{$cep}|{$ie}||||{$ddd}|{$tel}|||||||{$regime}|N||";
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

            if (!isset($this->catalogoProdutos[$chave])) {
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

            $campos0100 = [
                '0100', $cod, $desc, '', $ncm, '', '', '',
                '1', $uCom, 'S', 'O', '', '', '', 'N', '', $vUn, '', '', '', '', '', 'M',
            ];
            while (count($campos0100) < 90) {
                $campos0100[] = '';
            }
            $campos0100[] = $cod; // campo 91 = código interno (nunca EAN)
            $linhas[] = '|' . implode('|', $campos0100) . '|';

            // 0150 — unidade de medida (uma vez por sigla)
            if (!isset($this->unidadesGeradas[$uComNorm])) {
                $this->unidadesGeradas[$uComNorm] = true;
                $descUn = self::UNIDADE_DESC[$uComNorm] ?? $uComNorm;
                $linhas[] = "|0150|{$uCom}|{$descUn}|";
            }
        }
    }

    // ─── Registros da NF (1000, 1010, 1020, 1030, 1200, 1500) ────

    private function gerarRegistrosNf(NotaFiscalEletronica $notaFiscal, array &$linhas): array
    {
        $avisosIpiBc = [];
        $xmlData = $this->extrairXmlDados($notaFiscal);
        $infNFe = $xmlData['infNFe'] ?? [];

        // Dados da NF
        $chave = $notaFiscal->chave ?? '';
        $nNF = $notaFiscal->nNF;
        $serie = $notaFiscal->serie;
        $dtEmi = $notaFiscal->data_emissao ? $notaFiscal->data_emissao->format('d/m/Y') : date('d/m/Y');
        $dtEnt = $notaFiscal->data_entrada ? $notaFiscal->data_entrada->format('d/m/Y') : $dtEmi;
        $ufEmit = $notaFiscal->enderEmit_UF ?? '';
        $isSimples = ($notaFiscal->emitente_crt ?? '3') === '1';
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

        // Para cada tag, gerar itens com flags adequadas
        $todasNotasProcessadas = [];
        foreach ($tags as $tagged) {
            $tagId = $tagged->tag_id;
            $tagModel = $tagged->tag;
            if (!$tagModel) {
                continue;
            }

            $produtos = $notaFiscal->produtos;
            $itensProcessados = [];

            // Determinar flags por tag
            $credIcms = !$this->resolvedorCfop->isZeraIcms($tagId);
            $credIpi = !$this->resolvedorCfop->isZeraIpi($tagId);
            $credPiscof = !$isSimples; // SN não tem PIS/COFINS
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
                $item = new ItemNfeDto(
                    nItem: $item->nItem, cProd: $item->cProd, xProd: $item->xProd,
                    NCM: $item->NCM, uCom: $item->uCom, uComNorm: $item->uComNorm,
                    qCom: $item->qCom, vUnCom: $item->vUnCom,
                    cfopSaida: $item->cfopSaida, cfopEntrada: $cfopEntrada,
                    ean: $item->ean,
                    vProd: $item->vProd, vFrete: $item->vFrete, vSeg: $item->vSeg,
                    vDesc: $item->vDesc, vOutro: $item->vOutro,
                    vII: $item->vII, vIPI: $item->vIPI,
                    vICMSDeson: $item->vICMSDeson, vPISST: $item->vPISST, vCOFINSST: $item->vCOFINSST,
                    icmsCsosn: $item->icmsCsosn, icmsCst: $item->icmsCst,
                    icmsVBC: $item->icmsVBC, icmsPICMS: $item->icmsPICMS,
                    icmsVICMS: $item->icmsVICMS, icmsVST: $item->icmsVST,
                    icmsPRedBC: $item->icmsPRedBC, icmsVCredSN: $item->icmsVCredSN,
                    icmsPCredSN: $item->icmsPCredSN,
                    icmsVBCEfet: $item->icmsVBCEfet, icmsPICMSEfet: $item->icmsPICMSEfet,
                    icmsVICMSEfet: $item->icmsVICMSEfet,
                    icmsVIPI: $item->icmsVIPI, ipiPIPI: $item->ipiPIPI, ipiVBC: $item->ipiVBC,
                    pisPPIS: $item->pisPPIS, pisVPIS: $item->pisVPIS, pisCst: $item->pisCst,
                    cofPCOFINS: $item->cofPCOFINS, cofVCOFINS: $item->cofVCOFINS, cofCst: $item->cofCst,
                    ibsCClass: $item->ibsCClass, ibsBC: $item->ibsBC,
                    ibsAliq: $item->ibsAliq, ibsVal: $item->ibsVal,
                    cbsCClass: $item->cbsCClass, cbsBC: $item->cbsBC,
                    cbsAliq: $item->cbsAliq, cbsVal: $item->cbsVal,
                    isSimples: $isSimples, credIcms: $credIcms, credPiscof: $credPiscof,
                    ipiNaBc: $item->ipiNaBc,
                );

                $itensProcessados[] = $item;
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
                if (!empty($infCpl)) {
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
                if ($isSimples && !$credIcms && $seg->nSeg === 0) {
                    $linhas[] = '|1200|0,00|0,00|0,00|';
                }
            }

            // 1500 — Parcelas
            $parcelas = $notaFiscal->parcelas;
            foreach ($parcelas as $dup) {
                $dVenc = $dup['dVenc'] ?? '';
                $vDup = $dup['vDup'] ?? '0,00';
                $nDup = $dup['nDup'] ?? '';
                if (!empty($dVenc)) {
                    $dVencFmt = $this->fmtData($dVenc);
                    $linhas[] = "|1500|{$dVencFmt}|{$vDup}|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|0,00|{$nDup}|";
                }
            }
        }

        return $avisosIpiBc;
    }

    // ─── Registro 1000 ───────────────────────────────────────────

    private function gerar1000(SegmentoDto $seg, NotaFiscalEletronica $notaFiscal, string $codSituacao, string $modFrete, string $dtEmi, string $dtEnt, string $infAdFisco, string $infCpl, string $chave): string
    {
        $campos = array_fill(0, 90, '');
        $campos[0] = '1000';
        $campos[1] = '36'; // espécie
        $campos[2] = $notaFiscal->emitente_cnpj ?? ''; // CNPJ fornecedor
        $campos[4] = (string) $seg->acumulador;
        $campos[5] = $seg->cfop;
        $campos[6] = (string) $seg->nSeg; // segmento
        $campos[7] = $notaFiscal->nNF ?? '';
        $campos[8] = $notaFiscal->serie ?? '';
        $campos[10] = $dtEnt; // data entrada
        $campos[11] = $dtEmi; // data emissão
        $campos[12] = $this->fmtDec($seg->vContabil()); // vContabil
        $campos[14] = mb_substr($infAdFisco, 0, 250);
        $campos[15] = $modFrete;
        $campos[16] = 'T'; // emitente = Terceiros
        $campos[25] = $this->fmtDec($seg->vFrete); // frete
        $campos[26] = $this->fmtDec($seg->vSeg); // seguro
        $campos[27] = $this->fmtDec($seg->vDesc); // desconto
        $campos[28] = $this->fmtDec($seg->vOutro); // outras despesas
        $campos[38] = $this->fmtDec($seg->vProd); // valor produtos
        $campos[40] = $codSituacao; // situação documento
        $campos[53] = $chave; // chave NF-e
        $campos[61] = mb_substr($infCpl, 0, 250); // infCpl
        if ($seg->vIPI > 0) {
            $campos[89] = $this->fmtDec($seg->vIPI); // vIPI
        }

        return '|' . implode('|', $campos) . '|';
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
        $temIcms1020 = $credIcms && !$isSimples;
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
        $credIpi = !$this->resolvedorCfop->isZeraIpi($tagId);
        $cstIpi = ($credIpi && !$isSimples && $item->icmsVIPI > 0) ? '00' : '49';

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

        return '|' . implode('|', $c) . '|';
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function extrairXmlDados(NotaFiscalEletronica $notaFiscal): array
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
            $service = new \App\Services\Xml\XmlReaderService;
            return $service->read($uncompressed);
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