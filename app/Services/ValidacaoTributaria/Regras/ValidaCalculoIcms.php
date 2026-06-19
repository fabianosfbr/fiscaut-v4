<?php

namespace App\Services\ValidacaoTributaria\Regras;

use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

class ValidaCalculoIcms implements RegraValidacaoInterface
{
    private const CST_ISENTOS_SEM_CALCULO = ['40', '41', '50', '60', '90'];

    private const CSOSN_ISENTOS_SEM_CALCULO = ['102', '300', '400', '500'];

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
            $csosn = (string) ($produto['CSOSN'] ?? '');
            $cst = (string) ($impostos['CST'] ?? '');

            if (in_array($csosn, self::CSOSN_ISENTOS_SEM_CALCULO, true)) {
                continue;
            }

            $cst2 = str_pad($cst, 2, '0', STR_PAD_LEFT);
            if (strlen($cst) >= 2 && in_array($cst2, self::CST_ISENTOS_SEM_CALCULO, true)) {
                continue;
            }

            $vBC = (float) ($impostos['vBC'] ?? 0);
            $pICMS = (float) ($impostos['pICMS'] ?? 0);
            $vICMS = (float) ($impostos['vICMS'] ?? 0);

            if ($vBC <= 0 || $pICMS <= 0) {
                continue;
            }

            $vICMSCalculado = round($vBC * $pICMS / 100, 2);
            $diferenca = abs($vICMS - $vICMSCalculado);

            if ($diferenca > $this->tolerancia) {
                $resultados[] = new ResultadoValidacao(
                    regra: 'calculo_icms',
                    tipoImposto: 'ICMS',
                    nItem: $nItem,
                    severidade: SeveridadeValidacaoEnum::AVISO,
                    mensagem: "Produto '{$xProd}': divergência no cálculo do ICMS.",
                    valorEsperado: number_format($vICMSCalculado, 2, ',', '.'),
                    valorEncontrado: number_format($vICMS, 2, ',', '.'),
                );
            }
        }

        return $resultados;
    }

    public function nome(): string
    {
        return 'calculo_icms';
    }

    public function descricao(): string
    {
        return 'Valida se vICMS ≈ vBC × pICMS / 100 para cada produto.';
    }
}
