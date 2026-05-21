<?php

namespace App\Imports;

use App\Models\HistoricoContabil;
use App\Models\ParametroSuperLogica;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class OptimizedExcelSuperLogicaImport
{
    private string $filePath;

    private string $currentSection = '';

    private string $currentCategory = '';

    private array $records = [];

    private const SECAO_RECEITAS = 'Receitas';

    private const SECAO_DESPESAS = 'Despesas';

    private const LABEL_TOTAL = 'Total de ';

    private const MAX_COLUMN = 14; // coluna N

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function read(): Collection
    {
        $this->records = [];
        $this->currentSection = '';
        $this->currentCategory = '';

        $reader = new XlsxReader;
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new class(self::MAX_COLUMN) implements IReadFilter
        {
            public function __construct(private int $maxColumn) {}

            public function readCell(string $column, int $row, string $worksheetName = ''): bool
            {
                return Coordinate::columnIndexFromString($column) <= $this->maxColumn;
            }
        });

        $spreadsheet = $reader->load($this->filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        for ($row = 1; $row <= $highestRow; $row++) {
            $cells = $this->readRowCells($sheet, $row);

            if ($this->isSectionHeader($cells)) {
                $this->currentSection = trim((string) $cells['A']);
                $this->currentCategory = '';

                continue;
            }

            if ($this->currentSection === '') {
                continue;
            }

            if ($this->isIgnorableRow($cells)) {
                continue;
            }

            if ($this->isTotalRow($cells)) {
                continue;
            }

            $valor = $this->getValor($cells);

            if ($valor !== null && abs($valor) > 0.001) {
                $this->records[] = $this->extractRecord($cells, $valor);

                continue;
            }

            if ($this->isCategoryRow($cells)) {
                $this->currentCategory = trim((string) $cells['A']);

                continue;
            }
        }

        return collect($this->records);
    }

    public function getRecords(): Collection
    {
        if (empty($this->records)) {
            return $this->read();
        }

        return collect($this->records);
    }

    public function getSections(): array
    {
        if (empty($this->records)) {
            $this->read();
        }

        return [
            'receitas' => collect(array_filter($this->records, fn ($r) => $r['secao'] === self::SECAO_RECEITAS)),
            'despesas' => collect(array_filter($this->records, fn ($r) => $r['secao'] === self::SECAO_DESPESAS)),
        ];
    }

    private function readRowCells($sheet, int $row): array
    {
        $cells = [];
        for ($col = 1; $col <= self::MAX_COLUMN; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $cell = $sheet->getCell($colLetter.$row);
            $cells[$colLetter] = $cell->isFormula() ? $cell->getOldCalculatedValue() : $cell->getValue();
        }

        return $cells;
    }

    private function isSectionHeader(array $cells): bool
    {
        $a = trim((string) ($cells['A'] ?? ''));

        return in_array($a, [self::SECAO_RECEITAS, self::SECAO_DESPESAS], true)
            && ! empty($cells['B']);
    }

    private function isIgnorableRow(array $cells): bool
    {
        $a = trim((string) ($cells['A'] ?? ''));

        if ($a === '' && $cells['B'] === null && $cells['C'] === null) {
            return true;
        }

        if (
            str_contains($a, 'Demonstrativo de Receitas e Despesas')
            || str_contains($a, 'Entre ')
            || str_contains($a, 'Saldo em ')
            || str_contains($a, 'Mov. Líquido')
            || str_contains($a, 'Resumo Financeiro')
            || str_contains($a, 'Saldo final')
            || str_contains($a, 'Inclui transferência')
            || $a === 'Conta'
        ) {
            return true;
        }

        return false;
    }

    private function isTotalRow(array $cells): bool
    {
        $a = trim((string) ($cells['A'] ?? ''));

        return str_starts_with($a, self::LABEL_TOTAL);
    }

    private function isCategoryRow(array $cells): bool
    {
        $a = trim((string) ($cells['A'] ?? ''));

        if ($a === '') {
            return false;
        }

        if ($this->looksLikeSubHeader($cells)) {
            return false;
        }

        if (preg_match('/^\d/', $a)) {
            return false;
        }

        return true;
    }

    private function looksLikeSubHeader(array $cells): bool
    {
        $knownLabels = ['competência', 'liquidação', 'crédito', 'documento', 'forma de pgto.', 'conta bancária', 'valor', 'data', 'debito', 'credito', 'cod.', 'historico'];

        foreach (['B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $val = mb_strtolower(trim((string) ($cells[$col] ?? '')));
            if (in_array($val, $knownLabels, true)) {
                return true;
            }
        }

        return false;
    }

    private function extractRecord(array $cells, ?float $valor = null): array
    {
        $isReceita = $this->currentSection === self::SECAO_RECEITAS;

        return [
            'secao' => $this->currentSection,
            'categoria' => $this->currentCategory,
            'descricao' => trim((string) ($cells['A'] ?? '')),
            'competencia' => $this->extractCompetencia($cells),
            'liquidacao' => $this->extractLiquidacao($cells, $isReceita),
            'credito' => $isReceita ? $this->extractCreditoReceita($cells) : null,
            'documento' => $isReceita ? null : $this->extractDocumento($cells),
            'forma_pagamento' => $isReceita ? null : $this->extractFormaPagamento($cells),
            'conta_bancaria' => $isReceita ? null : $this->extractContaBancaria($cells),
            'valor' => $valor ?? $this->getValor($cells),
        ];
    }

    private function extractCompetencia(array $cells): ?string
    {
        $b = $cells['B'] ?? null;

        if ($b !== null && preg_match('/^\d{2}\/\d{4}$/', trim((string) $b))) {
            return trim((string) $b);
        }

        return null;
    }

    private function extractLiquidacao(array $cells, bool $isReceita): ?string
    {
        $c = $cells['C'] ?? null;

        if ($c !== null) {
            $val = trim((string) $c);
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $val)) {
                return $val;
            }
        }

        return null;
    }

    private function extractCreditoReceita(array $cells): ?string
    {
        $d = $cells['D'] ?? null;

        if ($d !== null) {
            $val = trim((string) $d);
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $val)) {
                return $val;
            }
        }

        return null;
    }

    private function extractDocumento(array $cells): ?string
    {
        $d = $cells['D'] ?? null;

        if ($d !== null) {
            $val = trim((string) $d);
            if (! preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $val) && $val !== '') {
                return $val;
            }
        }

        return null;
    }

    private function extractFormaPagamento(array $cells): ?string
    {
        $e = $cells['E'] ?? null;

        if ($e !== null) {
            $val = trim((string) $e);
            if ($val !== '' && ! str_contains($val, '%')) {
                return $val;
            }
        }

        return null;
    }

    private function extractContaBancaria(array $cells): ?string
    {
        $f = $cells['F'] ?? null;

        if ($f !== null && is_string($f)) {
            $val = trim($f);
            if ($val !== '') {
                return $val;
            }
        }

        return null;
    }

    private function getValor(array $cells): ?float
    {
        $isReceita = $this->currentSection === self::SECAO_RECEITAS;

        $coluna = $isReceita ? 'F' : 'H';

        $raw = $cells[$coluna] ?? $cells['F'] ?? $cells['H'] ?? null;

        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            return (float) $raw;
        }

        return $this->parseBrazilianNumber((string) $raw);
    }

    private function parseBrazilianNumber(string $value): ?float
    {
        $value = trim($value);
        $value = str_replace(' ', '', $value);
        $value = str_replace('%', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    public function prepareData(array $rows, int $issuerId): array
    {
        $parametros = ParametroSuperLogica::where('issuer_id', $issuerId)->get();
        $historicos = HistoricoContabil::where('issuer_id', $issuerId)
            ->get()
            ->keyBy('codigo');

        $prepared = [];

        foreach ($rows as $row) {
            $match = $this->findParametroMatch($parametros, $row);

            $contaCredito = $match?->contaCredito?->codigo;
            $contaDebito = $match?->contaDebito?->codigo;
            $contaCreditoNome = $match?->contaCredito?->nome;
            $contaDebitoNome = $match?->contaDebito?->nome;

            if ($match?->check_value && $row['valor'] < 0) {
                $tmp = $contaCredito;
                $contaCredito = $contaDebito;
                $contaDebito = $tmp;

                $tmp = $contaCreditoNome;
                $contaCreditoNome = $contaDebitoNome;
                $contaDebitoNome = $tmp;
            }

            $codigoHistorico = $match?->codigo_historico;
            $historicoTemplate = $codigoHistorico ? ($historicos[$codigoHistorico]->descricao ?? null) : null;
            $historico = $historicoTemplate ? $this->resolveHistorico($historicoTemplate, $row) : null;

            $prepared[] = [
                'secao' => $row['secao'] ? $this->normalizeText($row['secao']) : null,
                'categoria' => $row['categoria'] ? $this->normalizeText($row['categoria']) : null,
                'descricao' => $row['descricao'] ?? null,
                'competencia' => $this->formatDate($row['competencia']) ?? null,
                'liquidacao' => $this->formatDate($row['liquidacao']) ?? null,
                'credito' => $this->formatDate($row['credito']) ?? null,
                'documento' => $row['documento'] ?? null,
                'conta_bancaria' => $row['conta_bancaria'] ?? null,
                'valor' => is_numeric($row['valor']) ? (float) $row['valor'] : $row['valor'],
                'conta_credito' => $contaCredito,
                'conta_debito' => $contaDebito,
                'conta_credito_nome' => $contaCreditoNome,
                'conta_debito_nome' => $contaDebitoNome,
                'codigo_historico' => $codigoHistorico,
                'historico' => $historico,
            ];
        }

        return $prepared;
    }

    private function findParametroMatch($parametros, array $row): ?ParametroSuperLogica
    {
        $categoria = $this->normalizeMatchText($row['categoria'] ?? null);
        $descricao = $this->normalizeMatchText($row['descricao'] ?? null);

        if (! $categoria && ! $descricao) {
            return null;
        }

        $matchTargets = array_filter([$categoria, $descricao]);
        $bestMatch = null;
        $bestTermsCount = 0;

        foreach ($parametros as $parametro) {
            $terms = collect($parametro->params ?? [])
                ->map(fn ($term) => $this->normalizeMatchText($term))
                ->filter()
                ->values()
                ->all();

            if ($terms === []) {
                continue;
            }

            $matchedAllTerms = collect($terms)->every(
                fn (string $term) => in_array($term, $matchTargets, true)
            );

            if ($matchedAllTerms) {
                $termsCount = count($terms);

                if ($termsCount > $bestTermsCount) {
                    $bestMatch = $parametro;
                    $bestTermsCount = $termsCount;
                }
            }
        }

        return $bestMatch;
    }

    private function normalizeMatchText($value): ?string
    {
        $text = $this->normalizeText($value);

        if ($text === null) {
            return null;
        }

        $text = preg_replace('/[^A-Z0-9]+/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text === '' ? null : $text;
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
}
