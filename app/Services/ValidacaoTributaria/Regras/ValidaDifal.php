<?php

namespace App\Services\ValidacaoTributaria\Regras;

use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

class ValidaDifal implements RegraValidacaoInterface
{
    private const CST_TRIBUTADOS = ['00', '10', '20', '90'];

    public function validar(array $produtos, array $nota, Issuer $issuer): array
    {
        $resultados = [];

        $ufEmitente = $nota['emitente_uf'] ?? '';
        $ufDestinatario = $nota['destinatario_uf'] ?? '';
        $vICMSUFDest = (float) ($nota['vICMSUFDest'] ?? 0);

        $ufEmitente = strtoupper(trim($ufEmitente));
        $ufDestinatario = strtoupper(trim($ufDestinatario));

        if ($ufEmitente === '' || $ufDestinatario === '') {
            return $resultados;
        }

        if ($ufEmitente === $ufDestinatario) {
            return $resultados;
        }

        $tpNf = (string) ($nota['tpNf'] ?? '');
        $cfopsNota = $this->extrairCfops($nota);

        if ($tpNf === '1' && ! $this->possuiCfopTributadoInterestadual($cfopsNota)) {
            return $resultados;
        }

        if ($vICMSUFDest > 0) {
            return $resultados;
        }

        $temIcmsTributado = false;
        foreach ($produtos as $produto) {
            $impostos = $produto['impostos'] ?? [];
            $cst = (string) ($impostos['CST'] ?? '');
            $vICMS = (float) ($impostos['vICMS'] ?? 0);

            $cst2 = str_pad($cst, 2, '0', STR_PAD_LEFT);

            if (in_array($cst2, self::CST_TRIBUTADOS, true) && $vICMS > 0) {
                $temIcmsTributado = true;
                break;
            }
        }

        if ($temIcmsTributado) {
            $resultados[] = new ResultadoValidacao(
                regra: 'difal',
                tipoImposto: 'DIFAL',
                nItem: null,
                severidade: SeveridadeValidacaoEnum::AVISO,
                mensagem: "Operação interestadual ({$ufEmitente} → {$ufDestinatario}) com ICMS tributado, mas DIFAL não calculado (vICMSUFDest = 0).",
                valorEsperado: 'DIFAL > 0',
                valorEncontrado: 'DIFAL = 0,00',
            );
        }

        return $resultados;
    }

    public function nome(): string
    {
        return 'difal';
    }

    public function descricao(): string
    {
        return 'Valida se DIFAL foi calculado para operações interestaduais.';
    }

    private function extrairCfops(array $nota): array
    {
        if (isset($nota['cfops']) && is_array($nota['cfops'])) {
            return $nota['cfops'];
        }

        static $cache = [];
        $cacheKey = $nota['id'] ?? 0;

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $cfops = [];

        if (! empty($nota['id']) && ! empty($nota['chave'])) {
            $nfe = NotaFiscalEletronica::find($nota['id']);
            if ($nfe) {
                $cfops = (array) ($nfe->cfops ?? []);
            }
        }

        $cache[$cacheKey] = $cfops;

        return $cfops;
    }

    private function possuiCfopTributadoInterestadual(array $cfops): bool
    {
        foreach ($cfops as $cfop) {
            $cfop = (string) $cfop;

            if (strlen($cfop) === 4) {
                $prefixo = substr($cfop, 0, 1);
                if (in_array($prefixo, ['5', '6', '7'], true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
