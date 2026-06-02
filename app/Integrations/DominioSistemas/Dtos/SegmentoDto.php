<?php

namespace App\Integrations\DominioSistemas\Dtos;

/**
 * Representa um segmento da NF (agrupamento por CFOP de entrada)
 */
class SegmentoDto
{
    /** @param ItemNfeDto[] $itens */
    public function __construct(
        public readonly string $cfop,
        public readonly int $acumulador,
        public readonly array $itens,
        public readonly float $vProd,
        public readonly float $vFrete,
        public readonly float $vSeg,
        public readonly float $vDesc,
        public readonly float $vOutro,
        public readonly float $vIPI,
        public readonly float $vICMS,
        public readonly float $vST,
        public readonly float $vCredSN,
        public readonly int $nSeg = 1,
    ) {}

    /**
     * Cria segmentos a partir de uma lista de itens
     * @param ItemNfeDto[] $itens
     * @return self[]
     */
    public static function segmentar(array $itens, int $acumulador): array
    {
        $grupos = [];
        foreach ($itens as $item) {
            $cfop = $item->cfopEntrada;
            if (!isset($grupos[$cfop])) {
                $grupos[$cfop] = [];
            }
            $grupos[$cfop][] = $item;
        }

        $segmentos = [];
        $nSeg = 0;
        foreach ($grupos as $cfop => $itensGrupo) {
            $nSeg++;
            $vProd = array_sum(array_map(fn(ItemNfeDto $i) => $i->vProd, $itensGrupo));
            $vFrete = array_sum(array_map(fn(ItemNfeDto $i) => $i->vFrete, $itensGrupo));
            $vSeg = array_sum(array_map(fn(ItemNfeDto $i) => $i->vSeg, $itensGrupo));
            $vDesc = array_sum(array_map(fn(ItemNfeDto $i) => $i->vDesc, $itensGrupo));
            $vOutro = array_sum(array_map(fn(ItemNfeDto $i) => $i->vOutro, $itensGrupo));
            $vIPI = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVIPI, $itensGrupo));
            $vICMS = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVICMS, $itensGrupo));
            $vST = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVST, $itensGrupo));
            $vCredSN = array_sum(array_map(fn(ItemNfeDto $i) => $i->icmsVCredSN, $itensGrupo));

            $segmentos[] = new self(
                cfop: $cfop,
                acumulador: $acumulador,
                itens: $itensGrupo,
                vProd: $vProd,
                vFrete: $vFrete,
                vSeg: $vSeg,
                vDesc: $vDesc,
                vOutro: $vOutro,
                vIPI: $vIPI,
                vICMS: $vICMS,
                vST: $vST,
                vCredSN: $vCredSN,
                nSeg: count($grupos) > 1 ? $nSeg : 0,
            );
        }

        return $segmentos;
    }

    /**
     * Valor contábil do segmento: vProd + vFrete + vSeg + vOutro + vIPI - vDesc
     */
    public function vContabil(): float
    {
        return round($this->vProd + $this->vFrete + $this->vSeg + $this->vOutro + $this->vIPI - $this->vDesc, 2);
    }
}