<?php

namespace App\Integrations\DominioSistemas\Services;

use App\Integrations\DominioSistemas\Dtos\ItemNfeDto;
use App\Integrations\DominioSistemas\Dtos\SegmentoDto;

/**
 * Calcula ICMS, IPI e DIFAL para o registro 1020
 * Equivalente ao Python: gerar_1020.py + calcular_difal()
 */
class CalculadorIcmsService
{
    private const ALIQ_INTERNA_SP = 0.18;

    // CSTs que vão em isentas
    private const CST_ISENTAS = ['30', '40'];
    // CSTs que vão em outras
    private const CST_OUTRAS = ['50', '60', '61', '62', '90'];
    // CSTs tributados (tem BC + ICMS)
    private const CST_TRIBUTADOS = ['00', '10', '20', '51', '70'];
    // CSTs com ST
    private const CST_COM_ST = ['10', '70'];

    private ResolvedorCfopService $resolvedorCfop;

    public function __construct(ResolvedorCfopService $resolvedorCfop)
    {
        $this->resolvedorCfop = $resolvedorCfop;
    }

    /**
     * Gera as linhas 1020 ICMS (codigo 1)
     * @return string[]
     */
    public function gerar1020Icms(SegmentoDto $seg, bool $isSimples, bool $credIcms, string $notaUf): array
    {
        $linhas = [];
        $vCont = $seg->vContabil();
        $vContFmt = $this->fmtDec($vCont);

        // CSOSN / Simples Nacional
        if ($isSimples) {
            if ($credIcms) {
                // SN com crédito: BC + alíq + val no 1020, 1200 NÃO gerado (evita duplicidade)
                $baseSn = 0.0;
                $credSn = 0.0;
                foreach ($seg->itens as $item) {
                    if ($item->icmsVCredSN > 0) {
                        $baseSn += $item->vProd;
                        $credSn += $item->icmsVCredSN;
                    }
                }
                if ($baseSn > 0 && $credSn > 0) {
                    $aliqSn = round($credSn / $baseSn * 100, 2);
                    $linhas[] = "|1020|1|0,00|{$this->fmtDec($baseSn)}|{$this->fmtDec($aliqSn)}|{$this->fmtDec($credSn)}|0,00|0,00|0,00|0,00|{$vContFmt}|||||";
                } else {
                    // SN com cred_icms mas sem itens elegíveis
                    $linhas[] = "|1020|1|0,00|0,00|0,00|0,00|0,00|{$vContFmt}|0,00|0,00|{$vContFmt}|||||";
                }
            } else {
                // SN sem crédito: tudo em outras
                $linhas[] = "|1020|1|0,00|0,00|0,00|0,00|0,00|{$vContFmt}|0,00|0,00|{$vContFmt}|||||";
            }
            return $linhas;
        }

        // Etiqueta de DESPESA (credIcms=false / status_icms=true = zera ICMS)
        if (!$credIcms) {
            $linhas[] = "|1020|1|0,00|0,00|0,00|0,00|0,00|{$vContFmt}|0,00|0,00|{$vContFmt}|||||";
            return $linhas;
        }

        // Etiqueta de CUSTO/ESTOQUE (credIcms=true)
        // Agrupar por alíquota ICMS
        $grupos = []; // aliq -> array de itens
        foreach ($seg->itens as $item) {
            $cst2 = $this->doisUltimos($item->icmsCst);
            $aliq = round($item->icmsPICMS, 4);

            // CST 60 com crédito CAT 14/2009: tratar como tributado
            $ehCat14 = $item->icmsVBC > 0 && $cst2 === '60';
            if (!$ehCat14 && (in_array($cst2, self::CST_ISENTAS) || in_array($cst2, self::CST_OUTRAS))) {
                $aliq = 0.0;
            }
            $key = (string) $aliq;
            $grupos[$key][] = $item;
        }

        // vIPI total do segmento (distribuir proporcional)
        $vIPISegTotal = $seg->vIPI;
        $vProdSegTotal = $seg->vProd > 0 ? $seg->vProd : 1;

        krsort($grupos, SORT_NUMERIC);
        $nGrupos = count($grupos);
        $vContAcum = 0.0;
        $idx = 0;

        foreach ($grupos as $aliq => $grupo) {
            $idx++;
            $ultimo = ($idx === $nGrupos);

            $vBCg = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVBC, $grupo));
            $vICMSg = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVICMS, $grupo));
            $vSTg = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVST, $grupo));
            $vProdg = array_sum(array_map(fn(ItemNfeDto $i) => $i->vProd, $grupo));

            // IPI proporcional ao vProd do grupo
            $vIPIg = round($vIPISegTotal * $vProdg / $vProdSegTotal, 2);

            $vIsentasg = 0.0;
            $vOutrasg = 0.0;

            foreach ($grupo as $item) {
                $cst2 = $this->doisUltimos($item->icmsCst);
                $vprodi = $item->vProd;

                if (in_array($cst2, self::CST_ISENTAS)) {
                    $vIsentasg += $vprodi;
                } elseif (in_array($cst2, self::CST_OUTRAS)) {
                    $vOutrasg += $vprodi;
                } elseif ($cst2 === '20') {
                    // Redução de BC: valor reduzido vai em isentas
                    $pRedBC = $item->icmsPRedBC;
                    $vIsentasg += round($vprodi * $pRedBC / 100, 2);
                } elseif ($cst2 === '51') {
                    // Diferimento: quando vBC=0, vProd vai em outras
                    if ($item->icmsVBC <= 0) {
                        $vOutrasg += $vprodi;
                    }
                }
            }

            // Valor contábil desta linha
            if ($ultimo) {
                $vContg = round($vCont - $vContAcum, 2);
            } else {
                $vContg = round($vBCg + $vIsentasg + $vOutrasg + $vSTg + $vIPIg, 2);
                $vContAcum += $vContg;
            }

            $vContgFmt = $this->fmtDec($vContg);
            $linhas[] = "|1020|1|0,00|{$this->fmtDec($vBCg)}|{$this->fmtDec($aliq)}|{$this->fmtDec($vICMSg)}|{$this->fmtDec($vIsentasg)}|{$this->fmtDec($vOutrasg)}|{$this->fmtDec($vIPIg)}|{$this->fmtDec($vSTg)}|{$vContgFmt}|||||";
        }

        return $linhas;
    }

    /**
     * Gera as linhas 1020 IPI (codigo 2)
     * @return string[]
     */
    public function gerar1020Ipi(SegmentoDto $seg, bool $isSimples, bool $credIpi): array
    {
        $linhas = [];
        $vCont = $seg->vContabil();

        // Agrupar por alíquota IPI
        $grupos = [];
        foreach ($seg->itens as $item) {
            $aliq = round($item->ipiPIPI, 4);
            $key = (string) $aliq;
            $grupos[$key][] = $item;
        }

        krsort($grupos, SORT_NUMERIC);
        $nGrupos = count($grupos);
        $vContAcum = 0.0;
        $idx = 0;

        foreach ($grupos as $aliq => $grupo) {
            $idx++;
            $ultimo = ($idx === $nGrupos);

            $vBCIpiG = array_sum(array_map(fn(ItemNfeDto $i) => $i->ipiVBC, $grupo));
            $vIPIG = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVIPI, $grupo));

            if ($ultimo) {
                $vContg = round($vCont - $vContAcum, 2);
            } else {
                $vContg = round($vBCIpiG + $vIPIG, 2);
                $vContAcum += $vContg;
            }

            if ($credIpi && !$isSimples && $vIPIG > 0) {
                // Com crédito: BC e vIPI normais
                $linhas[] = "|1020|2|0,00|{$this->fmtDec($vBCIpiG)}|{$this->fmtDec($aliq)}|{$this->fmtDec($vIPIG)}|0,00|0,00|0,00|0,00|{$this->fmtDec($vContg)}||||";
            } else {
                // Sem crédito: tudo em outras = vCont (não só vProd)
                $linhas[] = "|1020|2|0,00|0,00|{$this->fmtDec($aliq)}|0,00|0,00|{$this->fmtDec($vContg)}|0,00|0,00|{$this->fmtDec($vContg)}|1097|||";
            }
        }

        return $linhas;
    }

    /**
     * Gera as linhas 1020 DIFAL (codigo 8)
     * @return string[]
     */
    public function gerar1020Difal(SegmentoDto $seg, bool $isSimples, bool $debDifal): array
    {
        $linhas = [];
        $cfop = $seg->cfop;

        // CFOPs que geram DIFAL
        $cfopsDifal = ['2556', '2551', '2406']; // 2407 removido (retorno conserto)

        if (!in_array($cfop, $cfopsDifal) || !$debDifal) {
            return $linhas;
        }

        // Agrupar por alíquota interestadual
        $grupos = [];
        foreach ($seg->itens as $item) {
            $vBC = $item->icmsVBC;
            $vICMS = $item->icmsVICMS;
            $pICMS = round($item->icmsPICMS, 2);

            if ($vBC <= 0) {
                // SN ou sem destaque: usa vProd, sem origem
                $vBC = $item->vProd;
                $vICMS = 0.0;
                $pICMS = 0.0;
            }

            $key = (string) $pICMS;
            if (!isset($grupos[$key])) {
                $grupos[$key] = ['vBC' => 0.0, 'vICMS' => 0.0];
            }
            $grupos[$key]['vBC'] += $vBC;
            $grupos[$key]['vICMS'] += $vICMS;
        }

        $vCont = $seg->vContabil();

        foreach ($grupos as $aliqInter => $vals) {
            $vBC = round($vals['vBC'], 2);
            $vICMS = round($vals['vICMS'], 2);
            $baseSem = $vBC - $vICMS;
            $baseDup = round($baseSem / (1 - self::ALIQ_INTERNA_SP), 2);
            $icmsDst = round($baseDup * self::ALIQ_INTERNA_SP, 2);
            $difal = max(round($icmsDst - $vICMS, 2), 0.0);

            if ($difal > 0) {
                $linhas[] = "|1020|8|0,00|{$this->fmtDec($baseDup)}|" .
                    "{$this->fmtDec(self::ALIQ_INTERNA_SP * 100)}|{$this->fmtDec($difal)}|" .
                    "0,00|0,00|0,00|0,00|{$this->fmtDec($vCont)}|||" .
                    "|{$this->fmtDec($aliqInter)}|||";
            }
        }

        return $linhas;
    }

    /**
     * Calcula PIS/COFINS para um item (campos 41-44, 67 do 1030)
     */
    public function calcularPiscofItem(ItemNfeDto $item, bool $credPiscof, string $baseCreditoCampo67): array
    {
        $regime = 'LR'; // Kopron = Lucro Real
        $vProd = $item->vProd;

        // Importação: usar valores do XML
        $cfop = $item->cfopEntrada;
        if (in_array($cfop, ['3101', '3102'])) {
            return [
                'aliq_pis' => $this->fmtDec($item->pisPPIS, 4),
                'vlr_pis' => $this->fmtDec($item->pisVPIS),
                'aliq_cofins' => $this->fmtDec($item->cofPCOFINS, 4),
                'vlr_cofins' => $this->fmtDec($item->cofVCOFINS),
                'cst_pis' => '50',
                'bc_pis' => $this->fmtDec($vProd),
                'cst_cofins' => '50',
                'bc_cofins' => $this->fmtDec($vProd),
                'base_credito' => '',
            ];
        }

        // Simples Nacional: sem crédito
        if ($item->isSimples) {
            return $this->semCreditoPiscof();
        }

        // Sem direito a crédito
        if (!$credPiscof) {
            return $this->semCreditoPiscof();
        }

        // Com crédito
        if ($regime === 'LR') {
            $aliqPis = 1.65;
            $aliqCofins = 7.60;
        } else {
            $aliqPis = 0.65;
            $aliqCofins = 3.00;
        }

        $vlrPis = round($vProd * $aliqPis / 100, 2);
        $vlrCofins = round($vProd * $aliqCofins / 100, 2);

        $baseCredito = !empty($baseCreditoCampo67) ? $baseCreditoCampo67 : '';

        return [
            'aliq_pis' => $this->fmtDec($aliqPis, 4),
            'vlr_pis' => $this->fmtDec($vlrPis),
            'aliq_cofins' => $this->fmtDec($aliqCofins, 4),
            'vlr_cofins' => $this->fmtDec($vlrCofins),
            'cst_pis' => '50',
            'bc_pis' => $this->fmtDec($vProd),
            'cst_cofins' => '50',
            'bc_cofins' => $this->fmtDec($vProd),
            'base_credito' => $baseCredito,
        ];
    }

    private function semCreditoPiscof(): array
    {
        return [
            'aliq_pis' => '0,0000',
            'vlr_pis' => '0,00',
            'aliq_cofins' => '0,0000',
            'vlr_cofins' => '0,00',
            'cst_pis' => '70',
            'bc_pis' => '0,00',
            'cst_cofins' => '70',
            'bc_cofins' => '0,00',
            'base_credito' => '',
        ];
    }

    /**
     * Retorna os dois últimos dígitos do CST (sempre 3 dígitos)
     */
    private function doisUltimos(string $cst): string
    {
        $cst = str_pad(trim($cst), 3, '0', STR_PAD_LEFT);
        return substr($cst, -2);
    }

    private function fmtDec(float $v, int $c = 2): string
    {
        return number_format($v, $c, ',', '');
    }
}