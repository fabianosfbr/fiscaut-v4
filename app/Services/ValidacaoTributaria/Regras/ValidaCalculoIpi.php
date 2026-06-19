<?php

namespace App\Services\ValidacaoTributaria\Regras;

use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

class ValidaCalculoIpi implements RegraValidacaoInterface
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

            $vIPI = (float) ($impostos['vIPI'] ?? 0);

            if ($vIPI <= 0) {
                continue;
            }

            $cstIpi = $impostos['CST_IPI'] ?? null;
            $pIPI = (float) ($impostos['pIPI'] ?? 0);

            if ($pIPI <= 0) {
                continue;
            }

            $vBCIpi = (float) ($produto['vProd'] ?? 0);

            $vIPICalculado = round($vBCIpi * $pIPI / 100, 2);
            $diferenca = abs($vIPI - $vIPICalculado);

            if ($diferenca > $this->tolerancia) {
                $resultados[] = new ResultadoValidacao(
                    regra: 'calculo_ipi',
                    tipoImposto: 'IPI',
                    nItem: $nItem,
                    severidade: SeveridadeValidacaoEnum::AVISO,
                    mensagem: "Produto '{$xProd}': divergência no cálculo do IPI".($cstIpi ? " (CST {$cstIpi})." : '.'),
                    valorEsperado: number_format($vIPICalculado, 2, ',', '.'),
                    valorEncontrado: number_format($vIPI, 2, ',', '.'),
                );
            }
        }

        return $resultados;
    }

    public function nome(): string
    {
        return 'calculo_ipi';
    }

    public function descricao(): string
    {
        return 'Valida se vIPI ≈ vBC_IPI × pIPI / 100 para cada produto com IPI.';
    }
}
