<?php

namespace App\Services\Relatorios;

use Illuminate\Support\Str;

class ListagemProdutosService
{
    public static function accumulate(array &$accumulator, array $items): void
    {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $codigo = trim((string) ($item['cProd'] ?? ''));
            $descricao = trim((string) ($item['xProd'] ?? ''));
            $ncm = trim((string) ($item['NCM'] ?? ''));
            $cfop = trim((string) ($item['CFOP'] ?? ''));
            $unidade = trim((string) ($item['uCom'] ?? ''));
            $ean = trim((string) ($item['cEAN'] ?? ''));

            if ($codigo === '' && $descricao === '') {
                continue;
            }

            $keySource = implode('|', [
                $codigo,
                self::lower($descricao),
                $ncm,
                $cfop,
                $unidade,
                $ean,
            ]);

            $key = (string) Str::of($keySource)->trim()->lower()->replaceMatches('/\s+/', ' ');
            $id = sha1($key);

            if (! array_key_exists($id, $accumulator)) {
                $accumulator[$id] = [
                    'id' => $id,
                    'cProd' => $codigo !== '' ? $codigo : null,
                    'xProd' => $descricao !== '' ? $descricao : null,
                    'NCM' => $ncm !== '' ? $ncm : null,
                    'CFOP' => $cfop !== '' ? $cfop : null,
                    'uCom' => $unidade !== '' ? $unidade : null,
                    'cEAN' => $ean !== '' ? $ean : null,
                    'total_qCom' => 0.0,
                    'total_vProd' => 0.0,
                    'itens' => 0,
                ];
            } else {
                if (($accumulator[$id]['xProd'] ?? null) === null && $descricao !== '') {
                    $accumulator[$id]['xProd'] = $descricao;
                }
                if (($accumulator[$id]['cProd'] ?? null) === null && $codigo !== '') {
                    $accumulator[$id]['cProd'] = $codigo;
                }
            }

            $accumulator[$id]['total_qCom'] += self::normalizeNumber($item['qCom'] ?? null);
            $accumulator[$id]['total_vProd'] += self::normalizeNumber($item['vProd'] ?? null);
            $accumulator[$id]['itens'] += 1;
        }
    }

    public static function normalizeNumber(mixed $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        if (is_string($value)) {
            $value = trim($value);

            $hasDot = str_contains($value, '.');
            $hasComma = str_contains($value, ',');

            if ($hasDot && $hasComma) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } elseif ($hasComma) {
                $value = str_replace(',', '.', $value);
            }
        }

        if (! is_numeric($value)) {
            return 0.0;
        }

        $float = (float) $value;

        if (! is_finite($float)) {
            return 0.0;
        }

        return round($float, 6);
    }

    private static function lower(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value);
        }

        return strtolower($value);
    }
}

