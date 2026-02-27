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

    private const SECTION_RECEITAS = 'receitas';
    private const SECTION_DESPESAS = 'despesas';

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
            $norm = array_map(fn($v) => is_string($v) ? $this->normalize($v) : '', $row);
            if ($this->isHeaderRow($norm)) {
                return array_map(fn($v) => is_string($v) ? $v : '', $row);
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
            $normalizedIndex[$this->normalize((string)$original)] = $pos;
        }

        $targets = [
            'valor' => ['valor', 'valores'],
            'data' => ['data', 'data_lancamento'],
            'historico' => ['historico', 'historico_'],
            'documento' => ['documento', 'doc', 'numero_documento', 'n_documento'],
            'conta_bancaria' => ['conta_bancaria', 'conta_bancaria_', 'banco', 'conta', 'conta_corrente'],
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
        $s = preg_replace('/[^\d\-\+\(\)\,\.\%]/u', '', $s);
        $s = str_replace('%', '', $s);
        $negative = false;
        if (preg_match('/^\((.+)\)$/', $s, $m) === 1) {
            $s = $m[1];
            $negative = true;
        }
        $lastDot = strrpos($s, '.');
        $lastComma = strrpos($s, ',');

        if ($lastDot !== false && $lastComma !== false) {
            // If both exist, the last separator is the decimal separator.
            if ($lastDot > $lastComma) {
                // 1,234.56 -> decimal '.', thousands ','
                $s = str_replace(',', '', $s);
            } else {
                // 1.234,56 -> decimal ',', thousands '.'
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            }
        } elseif ($lastComma !== false) {
            $digitsAfter = strlen(substr($s, $lastComma + 1));
            // Heuristic: comma with 3 trailing digits is usually thousands separator (e.g. 1,985)
            if ($digitsAfter === 3) {
                $s = str_replace(',', '', $s);
            } else {
                $s = str_replace(',', '.', $s);
            }
        } elseif ($lastDot !== false) {
            $digitsAfter = strlen(substr($s, $lastDot + 1));
            // Heuristic: dot with 3 trailing digits is usually thousands separator (e.g. 1.985)
            if ($digitsAfter === 3) {
                $s = str_replace('.', '', $s);
            }
        }

        // If multiple dots remain, keep only the last as decimal separator.
        if (substr_count($s, '.') > 1) {
            $parts = explode('.', $s);
            $decimal = array_pop($parts);
            $s = implode('', $parts) . '.' . $decimal;
        }

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
            try {
                $numeric = (float) $value;

                if ($numeric <= 0) {
                    return null;
                }

                // Common timestamp cases (seconds / milliseconds)
                if ($numeric >= 1_000_000_000_000) {
                    return Carbon::createFromTimestampMs((int) round($numeric));
                }
                if ($numeric >= 1_000_000_000) {
                    return Carbon::createFromTimestamp((int) round($numeric));
                }

                // YYYYMMDD (e.g. 20260227) occasionally comes as an integer from Excel exports
                $isWholeNumber = abs($numeric - round($numeric)) < 1e-9;
                if ($isWholeNumber) {
                    $asInt = (int) round($numeric);
                    if ($asInt >= 19000101 && $asInt <= 21001231) {
                        $asString = (string) $asInt;
                        if (strlen($asString) === 8) {
                            $d = Carbon::createFromFormat('Ymd', $asString);
                            if ($d !== false) {
                                return $d->startOfDay();
                            }
                        }
                    }
                }

                // Excel serial date (1900 date system): days since 1899-12-31, with the 1900 leap-year bug.
                $days = (int) floor($numeric);
                $fraction = $numeric - $days;
                if ($days >= 60) {
                    $days -= 1;
                }

                $seconds = (int) round($fraction * 86400);
                if ($seconds >= 86400) {
                    $seconds -= 86400;
                    $days += 1;
                }

                $tz = config('app.timezone') ?: null;
                return Carbon::create(1899, 12, 31, 0, 0, 0, $tz)
                    ->addDays($days)
                    ->addSeconds($seconds);
            } catch (\Throwable $e) {
                return null;
            }
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
        $currentSection = null;
        foreach ($collection as $row) {
            $norm = array_map(fn($v) => is_string($v) ? $this->normalize($v) : '', $row);

            if ($this->isHeaderRow($norm)) {
                $started = true;
                $this->columnMap = $this->detectColumns($row);
                $currentCategory = null;
                $currentSection = $this->detectSectionFromHeader($norm) ?? $currentSection;
                continue;
            }

            if (!$started) {
                continue;
            }

            $leftTitle = $this->getString($this->valueByPos($row, 'receitas'));
            $competenciaVal = $this->getString($this->valueByPos($row, 'competencia'));
            $liquidacaoVal = $this->getString($this->valueByPos($row, 'liquidacao'));
            $creditoLeftVal = $this->getString($this->valueByPos($row, 'credito'));
            $valorLeftVal = $this->getString($this->valueByPos($row, 'valor'));
            if ($leftTitle && !$competenciaVal && !$liquidacaoVal && !$creditoLeftVal && !$valorLeftVal) {
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
                'documento' => $this->getString($this->valueByPos($row, 'documento')),
                'conta_bancaria' => $this->getString($this->valueByPos($row, 'conta_bancaria')),
                'categoria' => isset($currentCategory) ? $currentCategory : null,
                'competencia' => isset($competenciaVal) ? $competenciaVal : null,
                'receita' => isset($leftTitle) ? $leftTitle : null,
                'liquidacao' => $this->parseDate($this->valueByPos($row, 'liquidacao')),
                'credito_data' => $this->parseDate($this->valueByPos($row, 'credito_data')),
                'tipo' => $currentSection,

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
                    $hasAll = $hasAll && !is_null($v) && $v !== '';
                }
                if (!$hasAll) {
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
                    'documento' => $item['documento'],
                    'conta_bancaria' => $item['conta_bancaria'],
                    'tipo' => $item['tipo'],
                ];
            }
        }

        return $result;
    }

    public function prepareData(array $rows,  $issuerId): array
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
            $normTerms = array_values(array_filter(array_map(fn($t) => $this->toUpperAscii((string) $t), $terms), fn($t) => $t !== ''));

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
                if (!empty($value)) {
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
        if (!array_key_exists($field, $this->columnMap)) {
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

    private function detectSectionFromHeader(array $normalizedHeaderRow): ?string
    {
        if (in_array(self::SECTION_RECEITAS, $normalizedHeaderRow, true)) {
            return self::SECTION_RECEITAS;
        }
        if (in_array(self::SECTION_DESPESAS, $normalizedHeaderRow, true)) {
            return self::SECTION_DESPESAS;
        }
        return null;
    }
}
