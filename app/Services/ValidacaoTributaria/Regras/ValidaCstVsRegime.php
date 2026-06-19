<?php

namespace App\Services\ValidacaoTributaria\Regras;

use App\Enums\RegimesEmpresariaisEnum;
use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

class ValidaCstVsRegime implements RegraValidacaoInterface
{
    private const CSOSN_VALIDOS = ['101', '102', '103', '201', '202', '203', '300', '400', '500', '900'];

    public function validar(array $produtos, array $nota, Issuer $issuer): array
    {
        $resultados = [];
        $regime = $issuer->regime;
        $ehSimples = $regime === RegimesEmpresariaisEnum::SIMPLES_NACIONAL->value;

        foreach ($produtos as $produto) {
            $nItem = (int) ($produto['nItem'] ?? 0);
            $impostos = $produto['impostos'] ?? [];

            $cst = (string) ($impostos['CST'] ?? '');
            $csosn = (string) ($produto['CSOSN'] ?? '');
            $xProd = $produto['xProd'] ?? "Item {$nItem}";

            if ($ehSimples) {
                if ($cst !== '' && $cst !== '0') {
                    $resultados[] = new ResultadoValidacao(
                        regra: 'cst_vs_regime',
                        tipoImposto: 'ICMS',
                        nItem: $nItem,
                        severidade: SeveridadeValidacaoEnum::AVISO,
                        mensagem: "Produto '{$xProd}': regime Simples Nacional, mas possui CST ({$cst}) em vez de CSOSN.",
                        valorEsperado: 'CSOSN ('.implode(', ', self::CSOSN_VALIDOS).')',
                        valorEncontrado: "CST {$cst}",
                    );
                }

                if ($csosn !== '' && ! in_array($csosn, self::CSOSN_VALIDOS, true)) {
                    $resultados[] = new ResultadoValidacao(
                        regra: 'cst_vs_regime',
                        tipoImposto: 'ICMS',
                        nItem: $nItem,
                        severidade: SeveridadeValidacaoEnum::AVISO,
                        mensagem: "Produto '{$xProd}': CSOSN ({$csosn}) não reconhecido para Simples Nacional.",
                        valorEsperado: 'CSOSN válido (101, 102, 103, 201, 202, 203, 300, 400, 500, 900)',
                        valorEncontrado: "CSOSN {$csosn}",
                    );
                }
            } else {
                if ($csosn !== '' && $csosn !== '0') {
                    $resultados[] = new ResultadoValidacao(
                        regra: 'cst_vs_regime',
                        tipoImposto: 'ICMS',
                        nItem: $nItem,
                        severidade: SeveridadeValidacaoEnum::AVISO,
                        mensagem: "Produto '{$xProd}': regime {$regime}, mas possui CSOSN ({$csosn}) em vez de CST.",
                        valorEsperado: 'CST (00, 10, 20, 30, 40, 41, 50, 51, 60, 70, 90)',
                        valorEncontrado: "CSOSN {$csosn}",
                    );
                }
            }
        }

        return $resultados;
    }

    public function nome(): string
    {
        return 'cst_vs_regime';
    }

    public function descricao(): string
    {
        return 'Valida se o CST/CSOSN é compatível com o regime tributário do emitente.';
    }
}
