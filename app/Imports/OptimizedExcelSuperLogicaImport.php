<?php

namespace App\Imports;

use App\Models\HistoricoContabil;
use App\Models\ParametroSuperLogica;
use Illuminate\Support\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class OptimizedExcelSuperLogicaImport
{
    private string $file;

    private array $headers = [];

    private array $columnMap = [];

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->headers = $this->getHeaders();
        $this->columnMap = $this->detectColumns($this->headers);
    }

    private function getHeaders(): array
    {
        $rows = (new FastExcel)->withoutHeaders()->import($this->file);
        foreach ($rows as $index => $row) {
            $norm = array_map(fn ($v) => is_string($v) ? $this->normalize($v) : '', $row);
            if ($this->isHeaderRow($norm)) {
                return array_map(fn ($v) => is_string($v) ? $v : '', $row);
            }
        }

        return [];
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = preg_replace('/[\.\,\-\(\)\[\];:]/u', ' ', $value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = preg_replace('/\s+/u', ' ', $value);
        $value = str_replace(' ', '_', $value);

        return $value;
    }

    private function isHeaderRow(array $normalizedRow): bool
    {
        $rightTargets = ['data', 'debito', 'credito', 'valor'];
        $leftTargets = ['receitas', 'despesas', 'competencia', 'liquidacao', 'credito', 'valor'];
        $rightFound = 0;
        $leftFound = 0;
        foreach ($normalizedRow as $cell) {
            if (in_array($cell, $rightTargets, true)) {
                $rightFound++;
            }
            if (in_array($cell, $leftTargets, true)) {
                $leftFound++;
            }
        }

        return $rightFound >= 3 || $leftFound >= 4;
    }

    private function detectColumns(array $headers): array
    {
        $normalizedIndex = [];
        foreach ($headers as $pos => $original) {
            $normalizedIndex[$this->normalize((string) $original)] = $pos;
        }

        $targets = [
            'valor' => ['valor', 'valores'],
            'data' => ['data', 'data_lancamento'],
            'historico' => ['historico', 'historico_'],
        ];

        $map = [];
        foreach ($targets as $field => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $normalizedIndex)) {
                    $map[$field] = $normalizedIndex[$alias];
                    break;
                }
            }
        }

        // Left-side headers logic (Receitas/Despesas)
        foreach (['credito', 'liquidacao', 'receitas', 'despesas', 'competencia'] as $alias) {
            $key = $this->normalize($alias);
            if (array_key_exists($key, $normalizedIndex)) {
                if ($alias === 'credito') {
                    $map['credito_data'] = $normalizedIndex[$key];
                    $map['data_credito'] = $normalizedIndex[$key];
                } elseif ($alias === 'receitas' || $alias === 'despesas') {
                    $map['receitas'] = $normalizedIndex[$key]; // Use "receitas" as key for category title column
                } else {
                    $map[$alias] = $normalizedIndex[$key];
                }
            }
        }

        // Handle right-side data if present
        if (array_key_exists('data', $normalizedIndex)) {
            foreach (
                [
                    'debito' => ['debito', 'debito_contabil', 'debito_codigo'],
                    'credito' => ['credito', 'credito_contabil', 'credito_codigo'],
                ] as $field => $aliases
            ) {
                foreach ($aliases as $alias) {
                    if (array_key_exists($alias, $normalizedIndex)) {
                        $map[$field] = $normalizedIndex[$alias];
                        break;
                    }
                }
            }
        }

        return $map;
    }

    private function parseNumber($value): float
    {
        if (is_null($value)) {
            return 0.0;
        }
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }
        $s = preg_replace('/\s+/u', '', (string) $value);
        $negative = false;
        if (preg_match('/^\((.+)\)$/', $s, $m) === 1) {
            $s = $m[1];
            $negative = true;
        }
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $n = is_numeric($s) ? (float) $s : 0.0;

        return $negative ? -$n : $n;
    }

    private function parseDate($value): ?Carbon
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            try {
                return Carbon::instance($value);
            } catch (\Throwable $e) {
                return null;
            }
        }
        if (is_int($value) || is_float($value)) {
            // TODO: Implementar
        }
        $v = trim((string) $value);
        $formats = ['d/m/Y H:i:s', 'd/m/Y', 'Y-m-d', 'Y-m-d H:i:s'];
        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $v);
                if ($d !== false) {
                    return $d;
                }
            } catch (\Throwable $e) {
            }
        }
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function import(): array
    {
        $collection = (new FastExcel)->withoutHeaders()->import($this->file);
        $result = [];

        $started = false;
        $currentCategory = null;
        foreach ($collection as $row) {
            $norm = array_map(fn ($v) => is_string($v) ? $this->normalize($v) : '', $row);

            if ($this->isHeaderRow($norm)) {
                $started = true;
                $this->columnMap = $this->detectColumns($row);

                continue;
            }

            if (! $started) {
                continue;
            }

            $leftTitle = $this->getString($this->valueByPos($row, 'receitas'));
            $competenciaVal = $this->getString($this->valueByPos($row, 'competencia'));
            $liquidacaoVal = $this->getString($this->valueByPos($row, 'liquidacao'));
            $creditoLeftVal = $this->getString($this->valueByPos($row, 'credito'));
            $valorLeftVal = $this->getString($this->valueByPos($row, 'valor'));
            if ($leftTitle && ! $competenciaVal && ! $liquidacaoVal && ! $creditoLeftVal && ! $valorLeftVal) {
                if (preg_match('/^\\s*total\\b/i', $leftTitle) !== 1) {
                    $title = preg_replace('/\\s*\\([^)]+\\)\\s*$/', '', $leftTitle);
                    $currentCategory = $this->toUpperAscii($title);
                }

                continue;
            }

            $item = [
                'data_credito' => $this->parseDate($this->valueByPos($row, 'data_credito')),
                'data' => $this->parseDate($this->valueByPos($row, 'data')) ?? $this->parseDate($this->valueByPos($row, 'data_credito')) ?? $this->parseDate($this->valueByPos($row, 'liquidacao')),
                'valor' => $this->parseNumber($this->valueByPos($row, 'valor')),
                'debito' => $this->getString($this->valueByPos($row, 'debito')),
                'credito' => $this->getString($this->valueByPos($row, 'credito')),
                'categoria' => isset($currentCategory) ? $currentCategory : null,
                'competencia' => isset($competenciaVal) ? $competenciaVal : null,
                'receita' => isset($leftTitle) ? $leftTitle : null,
                'liquidacao' => $this->parseDate($this->valueByPos($row, 'liquidacao')),
                'credito_data' => $this->parseDate($this->valueByPos($row, 'credito_data')),

            ];

            $required = ['valor', 'data'];
            if (array_key_exists('debito', $this->columnMap)) {
                $required[] = 'debito';
            }
            if (array_key_exists('credito', $this->columnMap)) {
                $required[] = 'credito';
            }
            $hasAll = true;
            foreach ($required as $f) {
                $v = $item[$f];
                if ($f === 'valor') {
                    $hasAll = $hasAll && ($v !== 0.0);
                } else {
                    $hasAll = $hasAll && ! is_null($v) && $v !== '';
                }
                if (! $hasAll) {
                    break;
                }
            }
            if ($hasAll) {
                $result[] = [
                    'categoria' => $item['categoria'],
                    'receita' => $item['receita'],
                    'competencia' => $item['competencia'],
                    'liquidacao' => $item['liquidacao']?->format('Y-m-d'),
                    'credito' => $item['credito_data']?->format('Y-m-d'),
                    'valor' => $item['valor'],
                    'conta_debito' => $item['debito'],
                    'conta_credito' => $item['credito'],
                ];
            }
        }

        return $result;
    }

    public function prepareData(array $rows, $issuerId): array
    {
        $parametros = ParametroSuperLogica::where('issuer_id', $issuerId)
            ->with(['contaCredito', 'contaDebito'])
            ->orderBy('id')
            ->get();

        $historicos = HistoricoContabil::where('issuer_id', $issuerId)
            ->get()
            ->keyBy('codigo');

        $cache = [];
        foreach ($parametros as $p) {
            $terms = is_array($p->params) ? $p->params : [];
            $normTerms = array_values(array_filter(array_map(fn ($t) => $this->toUpperAscii((string) $t), $terms), fn ($t) => $t !== ''));

            foreach ($normTerms as $t) {
                $cache[$t] = [
                    'credito' => $p->contaCredito?->codigo,
                    'credito_descr' => $p->contaCredito?->nome,
                    'debito' => $p->contaDebito?->codigo,
                    'debito_descr' => $p->contaDebito?->nome,
                    'codigo_historico' => $p->codigo_historico,
                    'check_value' => $p->check_value,
                ];
            }
        }

        foreach ($rows as $i => &$row) {

            $categoria = $this->toUpperAscii((string) ($row['categoria'] ?? ''));
            if ($categoria !== '' && isset($cache[$categoria])) {
                $p = $cache[$categoria];

                $debito = $p['debito'];
                $debitoDescr = $p['debito_descr'];
                $credito = $p['credito'];
                $creditoDescr = $p['credito_descr'];

                if (($p['check_value'] ?? false) && ($row['valor'] ?? 0) < 0) {
                    $temp = $debito;
                    $debito = $credito;
                    $credito = $temp;

                    $tempDescr = $debitoDescr;
                    $debitoDescr = $creditoDescr;
                    $creditoDescr = $tempDescr;

                    $row['valor'] = abs($row['valor']);
                }

                $row['conta_debito'] = $row['conta_debito'] ?? $debito;
                $row['conta_debito_descr'] = $row['conta_debito_descr'] ?? $debitoDescr;

                $row['conta_credito'] = $row['conta_credito'] ?? $credito;
                $row['conta_credito_descr'] = $row['conta_credito_descr'] ?? $creditoDescr;

                $row['codigo_historico'] = $row['codigo_historico'] ?? $p['codigo_historico'];
            }

            $codigo = $row['codigo_historico'] ?? null;

            $historico = $historicos->where('codigo', $codigo)->first();

            if ($historico) {
                $template = $historico?->descricao ?? '';
                if ($template !== '') {
                    $row['historico'] = $this->buildHistoricoFromTemplate($template, $row);
                }
            }
        }

        return $rows;
    }

    private function buildHistoricoFromTemplate(string $template, array $row): string
    {
        $map = [];
        foreach ($row as $key => $value) {
            $token = strtoupper(str_replace('-', '_', $key));
            if (in_array($token, ['LIQUIDACAO', 'CREDITO', 'DATA_CREDITO', 'DATA'], true)) {
                if (! empty($value)) {
                    try {
                        $value = Carbon::parse((string) $value)->format('d/m/Y');
                    } catch (\Throwable $e) {
                    }
                }
            }
            $map["#{$token}"] = is_scalar($value) ? (string) $value : '';
        }

        return strtr($template, $map);
    }

    private function valueByPos(array $row, string $field)
    {
        if (! array_key_exists($field, $this->columnMap)) {
            return null;
        }
        $pos = $this->columnMap[$field];

        return $row[$pos] ?? null;
    }

    private function getString($value): ?string
    {
        if (is_null($value)) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }

    private function toUpperAscii(string $value): string
    {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $s = strtoupper($s);
        $s = preg_replace('/\\s+/', ' ', $s);

        return trim($s);
    }
}
