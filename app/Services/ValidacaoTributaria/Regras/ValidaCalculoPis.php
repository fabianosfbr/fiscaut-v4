<?php

namespace App\Services\ValidacaoTributaria\Regras;

use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

class ValidaCalculoPis implements RegraValidacaoInterface
{
    private const CST_SEM_CALCULO = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '70', '71', '72', '73', '74', '75', '98', '99'];

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

            $vPIS = (float) ($impostos['vPIS'] ?? 0);
            $pPIS = (float) ($impostos['pPIS'] ?? 0);

            if ($vPIS <= 0 || $pPIS <= 0) {
                continue;
            }

            $vBCPis = (float) ($produto['vProd'] ?? 0);
            $vPISCalculado = round($vBCPis * $pPIS / 100, 2);
            $diferenca = abs($vPIS - $vPISCalculado);

            if ($diferenca > $this->tolerancia) {
                $resultados[] = new ResultadoValidacao(
                    regra: 'calculo_pis',
                    tipoImposto: 'PIS',
                    nItem: $nItem,
                    severidade: SeveridadeValidacaoEnum::AVISO,
                    mensagem: "Produto '{$xProd}': divergência no cálculo do PIS.",
                    valorEsperado: number_format($vPISCalculado, 2, ',', '.'),
                    valorEncontrado: number_format($vPIS, 2, ',', '.'),
                );
            }
        }

        return $resultados;
    }

    public function nome(): string
    {
        return 'calculo_pis';
    }

    public function descricao(): string
    {
        return 'Valida se vPIS ≈ vBC_PIS × pPIS / 100 para cada produto.';
    }
}
