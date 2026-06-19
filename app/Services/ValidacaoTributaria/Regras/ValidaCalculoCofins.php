<?php

namespace App\Services\ValidacaoTributaria\Regras;

use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

class ValidaCalculoCofins implements RegraValidacaoInterface
{
    public function __construct(
        private readonly float $tolerancia = 0.01,
    ) {}

    public function validar(array $produtos, array $nota, Issuer $issuer): array
    {
        $resultados = [];

        foreach ($produtos as $produto) {
            $nItem = (int) ($produto['nItem'] ?? 0);
            $impostos = $produto['impostos'] ?? [];
            $xProd = $produto['xProd'] ?? "Item {$nItem}";

            $vCOFINS = (float) ($impostos['vCOFINS'] ?? 0);
            $pCOFINS = (float) ($impostos['pCOFINS'] ?? 0);

            if ($vCOFINS <= 0 || $pCOFINS <= 0) {
                continue;
            }

            $vBCCofins = (float) ($produto['vProd'] ?? 0);
            $vCOFINSCalculado = round($vBCCofins * $pCOFINS / 100, 2);
            $diferenca = abs($vCOFINS - $vCOFINSCalculado);

            if ($diferenca > $this->tolerancia) {
                $resultados[] = new ResultadoValidacao(
                    regra: 'calculo_cofins',
                    tipoImposto: 'COFINS',
                    nItem: $nItem,
                    severidade: SeveridadeValidacaoEnum::AVISO,
                    mensagem: "Produto '{$xProd}': divergência no cálculo do COFINS.",
                    valorEsperado: number_format($vCOFINSCalculado, 2, ',', '.'),
                    valorEncontrado: number_format($vCOFINS, 2, ',', '.'),
                );
            }
        }

        return $resultados;
    }

    public function nome(): string
    {
        return 'calculo_cofins';
    }

    public function descricao(): string
    {
        return 'Valida se vCOFINS ≈ vBC_COFINS × pCOFINS / 100 para cada produto.';
    }
}
