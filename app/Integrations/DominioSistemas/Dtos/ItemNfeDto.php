<?php

namespace App\Integrations\DominioSistemas\Dtos;

/**
 * DTO de item processado da NF-e
 * Corresponde ao dict do Python em ler_nfe() -> itens[]
 */
class ItemNfeDto
{
    public function __construct(
        public readonly ?string $nItem,
        public readonly string $cProd,
        public readonly string $xProd,
        public readonly string $NCM,
        public readonly string $uCom,
        public readonly string $uComNorm,
        public readonly float $qCom,
        public readonly float $vUnCom,
        public readonly string $cfopSaida,
        public readonly string $cfopEntrada,
        public readonly string $ean,
        public readonly float $vProd,
        public readonly float $vFrete,
        public readonly float $vSeg,
        public readonly float $vDesc,
        public readonly float $vOutro,
        public readonly float $vII,
        public readonly float $vIPI,
        public readonly float $vICMSDeson,
        public readonly float $vPISST,
        public readonly float $vCOFINSST,
        // ICMS
        public readonly string $icmsCsosn,
        public readonly string $icmsCst,
        public readonly float $icmsVBC,
        public readonly float $icmsPICMS,
        public readonly float $icmsVICMS,
        public readonly float $icmsVST,
        public readonly float $icmsPRedBC,
        public readonly float $icmsVCredSN,
        public readonly float $icmsPCredSN,
        public readonly float $icmsVBCEfet,
        public readonly float $icmsPICMSEfet,
        public readonly float $icmsVICMSEfet,
        public readonly float $icmsVIPI,
        public readonly float $ipiPIPI,
        public readonly float $ipiVBC,
        // PIS
        public readonly float $pisPPIS,
        public readonly float $pisVPIS,
        public readonly string $pisCst,
        // COFINS
        public readonly float $cofPCOFINS,
        public readonly float $cofVCOFINS,
        public readonly string $cofCst,
        // IBS/CBS
        public readonly string $ibsCClass,
        public readonly float $ibsBC,
        public readonly float $ibsAliq,
        public readonly float $ibsVal,
        public readonly string $cbsCClass,
        public readonly float $cbsBC,
        public readonly float $cbsAliq,
        public readonly float $cbsVal,
        // Flags
        public readonly bool $isSimples,
        public readonly bool $credIcms,
        public readonly bool $credPiscof,
        public readonly bool $ipiNaBc,
    ) {}

    public static function fromArray(array $data): self
    {
        $uCom = $data['uCom'] ?? 'UN';
        $vProd = (float) ($data['vProd'] ?? 0);
        $vIPI = (float) ($data['impostos']['vIPI'] ?? 0);
        $vBC = (float) ($data['impostos']['vBC'] ?? 0);

        // Detectar IPI incluído na BC do ICMS (condição: vBC ≈ vProd + vIPI com tolerância 0.02)
        $ipiNaBc = $vIPI > 0 && $vBC > 0 && abs($vBC - $vProd - $vIPI) < 0.02;

        return new self(
            nItem: $data['nItem'] ?? null,
            cProd: $data['cProd'] ?? '',
            xProd: $data['xProd'] ?? '',
            NCM: $data['NCM'] ?? '',
            uCom: $uCom,
            uComNorm: strtoupper(trim($uCom)),
            qCom: (float) ($data['qCom'] ?? 0),
            vUnCom: (float) ($data['vUnCom'] ?? 0),
            cfopSaida: $data['CFOP'] ?? $data['cfop_saida'] ?? '',
            cfopEntrada: $data['cfop_entrada'] ?? '',
            ean: ($data['cEAN'] ?? '') === 'SEM GTIN' ? '' : ($data['cEAN'] ?? ''),
            vProd: $vProd,
            vFrete: (float) ($data['vFrete'] ?? 0),
            vSeg: (float) ($data['vSeg'] ?? 0),
            vDesc: (float) ($data['vDesc'] ?? 0),
            vOutro: (float) ($data['vOutro'] ?? 0),
            vII: (float) ($data['vII'] ?? 0),
            vIPI: $vIPI,
            vICMSDeson: (float) ($data['vICMSDeson'] ?? 0),
            vPISST: (float) ($data['vPISST'] ?? 0),
            vCOFINSST: (float) ($data['vCOFINSST'] ?? 0),
            icmsCsosn: $data['CSOSN'] ?? ($data['impostos']['CSOSN'] ?? ''),
            icmsCst: (string) ($data['impostos']['CST'] ?? ''),
            icmsVBC: $vBC,
            icmsPICMS: (float) ($data['impostos']['pICMS'] ?? 0),
            icmsVICMS: (float) ($data['impostos']['vICMS'] ?? 0),
            icmsVST: (float) ($data['impostos']['vICMSSTRet'] ?? 0),
            icmsPRedBC: (float) ($data['impostos']['pRedBC'] ?? 0),
            icmsVCredSN: (float) ($data['impostos']['vCredICMSSN'] ?? 0),
            icmsPCredSN: (float) ($data['impostos']['pCredSN'] ?? 0),
            icmsVBCEfet: (float) ($data['impostos']['vBCEfet'] ?? 0),
            icmsPICMSEfet: (float) ($data['impostos']['pICMSEfet'] ?? 0),
            icmsVICMSEfet: (float) ($data['impostos']['vICMSEfet'] ?? 0),
            icmsVIPI: $vIPI,
            ipiPIPI: (float) ($data['impostos']['pIPI'] ?? 0),
            ipiVBC: (float) ($data['impostos']['vBC_IPI'] ?? 0),
            pisPPIS: (float) ($data['impostos']['pPIS'] ?? 0),
            pisVPIS: (float) ($data['impostos']['vPIS'] ?? 0),
            pisCst: (string) ($data['impostos']['CST_PIS'] ?? '50'),
            cofPCOFINS: (float) ($data['impostos']['pCOFINS'] ?? 0),
            cofVCOFINS: (float) ($data['impostos']['vCOFINS'] ?? 0),
            cofCst: (string) ($data['impostos']['CST_COFINS'] ?? '50'),
            ibsCClass: $data['impostos']['IBS_CClass'] ?? '',
            ibsBC: (float) ($data['impostos']['IBS_BC'] ?? 0),
            ibsAliq: (float) ($data['impostos']['IBS_Aliq'] ?? 0),
            ibsVal: (float) ($data['impostos']['IBS_Val'] ?? 0),
            cbsCClass: $data['impostos']['CBS_CClass'] ?? '',
            cbsBC: (float) ($data['impostos']['CBS_BC'] ?? 0),
            cbsAliq: (float) ($data['impostos']['CBS_Aliq'] ?? 0),
            cbsVal: (float) ($data['impostos']['CBS_Val'] ?? 0),
            isSimples: (bool) ($data['is_simples'] ?? false),
            credIcms: (bool) ($data['cred_icms'] ?? true),
            credPiscof: (bool) ($data['cred_piscof'] ?? true),
            ipiNaBc: $ipiNaBc,
        );
    }

    /**
     * Valor contábil do item: vProd + vFrete + vSeg + vOutro - vDesc
     */
    public function vContabil(): float
    {
        return round($this->vProd + $this->vFrete + $this->vSeg + $this->vOutro - $this->vDesc, 2);
    }

    /**
     * Retorna o CST/CSOSN para usar no registro 1030 campo 10
     */
    public function getCstOuCsosn(): string
    {
        return $this->isSimples ? $this->icmsCsosn : $this->icmsCst;
    }

    /**
     * Clona o item com novo CFOP de entrada e flags (para rateio)
     */
    public function withCfopAndFlags(string $cfopEntrada, bool $credIcms, bool $credPiscof, float $scale = 1.0): self
    {
        $scaleArray = function (float $v) use ($scale): float {
            return round($v * $scale, 4);
        };

        $data = [
            'nItem' => $this->nItem,
            'cProd' => $this->cProd,
            'xProd' => $this->xProd,
            'NCM' => $this->NCM,
            'uCom' => $this->uCom,
            'qCom' => $scaleArray($this->qCom),
            'vUnCom' => $this->vUnCom,
            'CFOP' => $this->cfopSaida,
            'cEAN' => $this->ean,
            'vProd' => $scaleArray($this->vProd),
            'vFrete' => $scaleArray($this->vFrete),
            'vSeg' => $scaleArray($this->vSeg),
            'vDesc' => $scaleArray($this->vDesc),
            'vOutro' => $scaleArray($this->vOutro),
            'CSOSN' => $this->icmsCsosn,
            'cfop_entrada' => $cfopEntrada,
            'is_simples' => $this->isSimples,
            'cred_icms' => $credIcms,
            'cred_piscof' => $credPiscof,
            'impostos' => [
                'vBC' => $scaleArray($this->icmsVBC),
                'CST' => $this->icmsCst,
                'pICMS' => $this->icmsPICMS,
                'vICMS' => $scaleArray($this->icmsVICMS),
                'vICMSSTRet' => $scaleArray($this->icmsVST),
                'vCredICMSSN' => $scaleArray($this->icmsVCredSN),
                'pCredSN' => $this->icmsPCredSN,
                'vIPI' => $scaleArray($this->icmsVIPI),
                'pIPI' => $this->ipiPIPI,
                'vBC_IPI' => $scaleArray($this->ipiVBC),
                'vPIS' => $scaleArray($this->pisVPIS),
                'pPIS' => $this->pisPPIS,
                'vCOFINS' => $scaleArray($this->cofVCOFINS),
                'pCOFINS' => $this->cofPCOFINS,
            ],
        ];

        return self::fromArray($data);
    }
}
