<?php

namespace App\Services\ValidacaoTributaria\Regras;

use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

class ValidaTotaisVsProdutos implements RegraValidacaoInterface
{
    public function __construct(
        private readonly float $tolerancia = 0.01,
    ) {}

    public function validar(array $produtos, array $nota, Issuer $issuer): array
    {
        $resultados = [];

        $somaVProd = 0.0;
        $somaVBC = 0.0;
        $somaVICMS = 0.0;
        $somaVIPI = 0.0;
        $somaVPIS = 0.0;
        $somaVCOFINS = 0.0;

        foreach ($produtos as $produto) {
            $impostos = $produto['impostos'] ?? [];

            $somaVProd += (float) ($produto['vProd'] ?? 0);
            $somaVBC += (float) ($impostos['vBC'] ?? 0);
            $somaVICMS += (float) ($impostos['vICMS'] ?? 0);
            $somaVIPI += (float) ($impostos['vIPI'] ?? 0);
            $somaVPIS += (float) ($impostos['vPIS'] ?? 0);
            $somaVCOFINS += (float) ($impostos['vCOFINS'] ?? 0);
        }

        $somaVProd = round($somaVProd, 2);
        $somaVBC = round($somaVBC, 2);
        $somaVICMS = round($somaVICMS, 2);
        $somaVIPI = round($somaVIPI, 2);
        $somaVPIS = round($somaVPIS, 2);
        $somaVCOFINS = round($somaVCOFINS, 2);

        $vProdNota = (float) ($nota['vProd'] ?? 0);
        if ($somaVProd > 0 && abs($somaVProd - $vProdNota) > $this->tolerancia) {
            $resultados[] = new ResultadoValidacao(
                regra: 'totais_vs_produtos',
                tipoImposto: 'GERAL',
                nItem: null,
                severidade: SeveridadeValidacaoEnum::AVISO,
                mensagem: 'Soma dos valores dos produtos diverge do total informado no cabeçalho.',
                valorEsperado: number_format($somaVProd, 2, ',', '.'),
                valorEncontrado: number_format($vProdNota, 2, ',', '.'),
            );
        }

        $vICMSNota = (float) ($nota['vICMS'] ?? 0);
        if ($somaVICMS > 0 && abs($somaVICMS - $vICMSNota) > $this->tolerancia) {
            $resultados[] = new ResultadoValidacao(
                regra: 'totais_vs_produtos',
                tipoImposto: 'ICMS',
                nItem: null,
                severidade: SeveridadeValidacaoEnum::AVISO,
                mensagem: 'Soma do ICMS dos produtos diverge do total informado no cabeçalho.',
                valorEsperado: number_format($somaVICMS, 2, ',', '.'),
                valorEncontrado: number_format($vICMSNota, 2, ',', '.'),
            );
        }

        $vIPINota = (float) ($nota['vIPI'] ?? 0);
        if ($somaVIPI > 0 && abs($somaVIPI - $vIPINota) > $this->tolerancia) {
            $resultados[] = new ResultadoValidacao(
                regra: 'totais_vs_produtos',
                tipoImposto: 'IPI',
                nItem: null,
                severidade: SeveridadeValidacaoEnum::AVISO,
                mensagem: 'Soma do IPI dos produtos diverge do total informado no cabeçalho.',
                valorEsperado: number_format($somaVIPI, 2, ',', '.'),
                valorEncontrado: number_format($vIPINota, 2, ',', '.'),
            );
        }

        $vPISNota = (float) ($nota['vPIS'] ?? 0);
        if ($somaVPIS > 0 && abs($somaVPIS - $vPISNota) > $this->tolerancia) {
            $resultados[] = new ResultadoValidacao(
                regra: 'totais_vs_produtos',
                tipoImposto: 'PIS',
                nItem: null,
                severidade: SeveridadeValidacaoEnum::AVISO,
                mensagem: 'Soma do PIS dos produtos diverge do total informado no cabeçalho.',
                valorEsperado: number_format($somaVPIS, 2, ',', '.'),
                valorEncontrado: number_format($vPISNota, 2, ',', '.'),
            );
        }

        $vCOFINSNota = (float) ($nota['vCOFINS'] ?? 0);
        if ($somaVCOFINS > 0 && abs($somaVCOFINS - $vCOFINSNota) > $this->tolerancia) {
            $resultados[] = new ResultadoValidacao(
                regra: 'totais_vs_produtos',
                tipoImposto: 'COFINS',
                nItem: null,
                severidade: SeveridadeValidacaoEnum::AVISO,
                mensagem: 'Soma do COFINS dos produtos diverge do total informado no cabeçalho.',
                valorEsperado: number_format($somaVCOFINS, 2, ',', '.'),
                valorEncontrado: number_format($vCOFINSNota, 2, ',', '.'),
            );
        }

        return $resultados;
    }

    public function nome(): string
    {
        return 'totais_vs_produtos';
    }

    public function descricao(): string
    {
        return 'Valida se totais do cabeçalho batem com a soma dos produtos.';
    }
}
