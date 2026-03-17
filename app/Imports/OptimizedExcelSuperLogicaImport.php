<?php

namespace App\Imports;

use App\Models\HistoricoContabil;
use App\Models\ParametroSuperLogica;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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

    }

    public function getData(): array
    {
        $rows = [];

        $collection = (new FastExcel())
            ->withoutHeaders()
            ->import($this->file, function ($row) {
                return $row;
            });

        $currentSection = null;
        $currentHeaderMap = null;
        $currentCategory = null;

        foreach ($collection as $index => $row) {
            $excelRow = $index + 1;
            $values = $this->normalizeRowValues($row);

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $headerInfo = $this->detectHeader($values);
            if ($headerInfo) {
                $currentSection = $headerInfo['section'];
                $currentHeaderMap = $headerInfo['map'];
                $currentCategory = null;
                continue;
            }

            if (!$currentSection || !$currentHeaderMap) {
                if ($this->containsSectionTitle($values, self::SECTION_RECEITAS)) {
                    $currentSection = self::SECTION_RECEITAS;
                } elseif ($this->containsSectionTitle($values, self::SECTION_DESPESAS)) {
                    $currentSection = self::SECTION_DESPESAS;
                }
                continue;
            }

            $descricao = $this->valueAt($values, $currentHeaderMap['descricao']);
            $competencia = $this->valueAt($values, $currentHeaderMap['competencia']);
            $liquidacao = $this->valueAt($values, $currentHeaderMap['liquidacao']);
            $valor = $this->valueAt($values, $currentHeaderMap['valor']);
            $extra1 = $this->valueAt($values, $currentHeaderMap['extra1'] ?? null);
            $extra2 = $this->valueAt($values, $currentHeaderMap['extra2'] ?? null);

            if ($this->isCategoryRow($descricao, [$competencia, $liquidacao, $valor, $extra1, $extra2])) {
                $currentCategory = $this->cleanText($descricao);
                continue;
            }

            if ($descricao === null && $valor === null) {
                continue;
            }

            $rows[] = [
                'excel_row' => $excelRow,
                'section' => $currentSection,
                'categoria' => $currentCategory,
                'descricao' => $this->cleanText($descricao),
                'competencia' => $this->parseDate($competencia),
                'liquidacao' => $this->parseDate($liquidacao),
                'credito' => $currentSection === self::SECTION_RECEITAS ? $this->cleanText($extra1) : null,
                'documento' => $currentSection === self::SECTION_DESPESAS ? $this->cleanText($extra1) : null,
                'conta_bancaria' => $currentSection === self::SECTION_DESPESAS ? $this->cleanText($extra2) : null,
                'valor' => $this->parseDecimal($valor),
                'is_total' => $this->isTotalRow($descricao),
            ];
        }

        return $rows;
    }

    public function prepareData(array $rows, int $issuerId): array
    {
        $parametros = ParametroSuperLogica::where('issuer_id', $issuerId)->get();
        $historicos = HistoricoContabil::where('issuer_id', $issuerId)
            ->get()
            ->keyBy('codigo');

        $prepared = [];

        foreach ($rows as $row) {
            if (($row['is_total'] ?? false) === true) {
                continue;
            }

            if (!isset($row['valor']) || $row['valor'] === null) {
                continue;
            }

            $match = $this->findParametroMatch($parametros, $row);


            $contaCredito = $match?->contaCredito?->codigo;
            $contaDebito = $match?->contaDebito?->codigo;

            if ($match?->check_value && $row['valor'] < 0) {
                $tmp = $contaCredito;
                $contaCredito = $contaDebito;
                $contaDebito = $tmp;
            }

            $codigoHistorico = $match?->codigo_historico;
            $historicoTemplate = $codigoHistorico ? ($historicos[$codigoHistorico]->descricao ?? null) : null;
            $historico = $historicoTemplate ? $this->resolveHistorico($historicoTemplate, $row) : null;

            $prepared[] = [
                'section' => $row['section'] ? $this->normalizeText($row['section']) : null,
                'categoria' => $row['categoria'] ? $this->normalizeText($row['categoria']) : null,
                'descricao' => $row['descricao'] ?? null,
                'competencia' => $row['competencia'] ?? null,
                'liquidacao' => $row['liquidacao'] ?? null,
                'credito' => $row['credito'] ?? null,
                'documento' => $row['documento'] ?? null,
                'conta_bancaria' => $row['conta_bancaria'] ?? null,
                'valor' => $row['valor'],
                'conta_credito' => $contaCredito,
                'conta_debito' => $contaDebito,
                'codigo_historico' => $codigoHistorico,
                'historico' => $historico,
                'is_total' => $row['is_total'] ?? false,
            ];

        }

        return $prepared;
    }

    private function detectHeader(array $values): ?array
    {
        $normalized = array_map([$this, 'normalizeText'], $values);

        $competencia = $this->findHeaderIndex($normalized, ['competencia', 'competência']);
        $liquidacao = $this->findHeaderIndex($normalized, ['liquidacao', 'liquidação']);
        $valor = $this->findHeaderIndex($normalized, ['valor', 'vlr', 'valor (r$)']);

        $receitas = $this->findHeaderIndex($normalized, ['receitas', 'receita']);
        $despesas = $this->findHeaderIndex($normalized, ['despesas', 'despesa']);

        if ($receitas !== null && $competencia !== null && $liquidacao !== null && $valor !== null) {
            $credito = $this->findHeaderIndex($normalized, ['credito', 'crédito']);
            return [
                'section' => self::SECTION_RECEITAS,
                'map' => [
                    'descricao' => $receitas,
                    'competencia' => $competencia,
                    'liquidacao' => $liquidacao,
                    'valor' => $valor,
                    'extra1' => $credito,
                ],
            ];
        }

        if ($despesas !== null && $competencia !== null && $liquidacao !== null && $valor !== null) {
            $documento = $this->findHeaderIndex($normalized, ['documento', 'doc']);
            $conta = $this->findHeaderIndex($normalized, ['conta bancaria', 'conta bancária', 'banco', 'conta']);
            return [
                'section' => self::SECTION_DESPESAS,
                'map' => [
                    'descricao' => $despesas,
                    'competencia' => $competencia,
                    'liquidacao' => $liquidacao,
                    'valor' => $valor,
                    'extra1' => $documento,
                    'extra2' => $conta,
                ],
            ];
        }

        return null;
    }

    private function findHeaderIndex(array $values, array $names): ?int
    {
        foreach ($values as $index => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            foreach ($names as $name) {
                if ($value === $this->normalizeText($name)) {
                    return $index;
                }
            }
        }

        return null;
    }

    private function containsSectionTitle(array $values, string $section): bool
    {
        $needle = $section === self::SECTION_RECEITAS ? 'receitas' : 'despesas';
        foreach ($values as $value) {
            if ($this->normalizeText($value) === $needle) {
                return true;
            }
        }
        return false;
    }

    private function isCategoryRow(?string $descricao, array $others): bool
    {
        if ($descricao === null || $descricao === '') {
            return false;
        }
        foreach ($others as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }
        return true;
    }

    private function isTotalRow(?string $descricao): bool
    {
        if ($descricao === null) {
            return false;
        }
        return Str::contains($this->normalizeText($descricao), 'total');
    }

    private function normalizeRowValues(array $row): array
    {
        return array_map(function ($value) {
            if ($value instanceof \DateTimeInterface) {
                return $value;
            }
            if (is_string($value)) {
                $trimmed = trim($value);
                return $trimmed === '' ? null : $trimmed;
            }
            return $value;
        }, $row);
    }

    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return false;
            }
        }
        return true;
    }

    private function valueAt(array $values, ?int $index)
    {
        if ($index === null) {
            return null;
        }
        return $values[$index] ?? null;
    }

    private function normalizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }
        $text = (string) $value;
        $text = trim($text);
        if ($text === '') {
            return null;
        }
        $text = Str::ascii($text);
        $text = mb_strtoupper($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }

    private function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function parseDate($value): ?Carbon
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $value)) {
                return Carbon::createFromFormat('d/m/Y', substr($value, 0, 10));
            }
            if (preg_match('/^\d{2}\/\d{4}$/', $value)) {
                return Carbon::createFromFormat('m/Y', $value)->startOfMonth();
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return Carbon::createFromFormat('Y-m-d', substr($value, 0, 10));
            }
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $value = str_replace(['R$', ' '], '', $value);
        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');
            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma && !$hasDot) {
            $value = str_replace(',', '.', $value);
        }

        $value = preg_replace('/[^0-9\.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function resolveHistorico(string $template, array $row): string
    {
        $replacements = [
            '#CATEGORIA' => $row['categoria'] ?? null,
            '#DESCRICAO' => $row['descricao'] ?? null,
            '#COMPETENCIA' => $this->formatDate($row['competencia'] ?? null),
            '#LIQUIDACAO' => $this->formatDate($row['liquidacao'] ?? null),
            '#CREDITO' => $row['credito'] ?? null,
            '#DOCUMENTO' => $row['documento'] ?? null,
            '#CONTA_BANCARIA' => $row['conta_bancaria'] ?? null,
            '#VALOR' => $this->formatValor($row['valor'] ?? null),
        ];

        $resolved = str_replace(array_keys($replacements), array_values($replacements), $template);
        return trim(preg_replace('/\s+/', ' ', $resolved));
    }

    private function formatDate($value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->format('d/m/Y');
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('d/m/Y');
        }
        if (is_string($value) && $value !== '') {
            return $value;
        }
        return null;
    }

    private function formatValor($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return number_format((float) $value, 2, ',', '.');
        }
        return (string) $value;
    }

    private function findParametroMatch($parametros, array $row): ?ParametroSuperLogica
    {
        $categoria = $this->normalizeText($row['categoria'] ?? null);

        if (!$categoria) {
            return null;
        }

        foreach ($parametros as $parametro) {
            $terms = $parametro->params ?? [];
            foreach ($terms as $term) {
                $termNormalized = $this->normalizeText($term);
                if ($termNormalized && $termNormalized === $categoria) {
                    return $parametro;
                }
            }
        }

        return null;
    }
}