<?php

namespace App\Services\Contabil;

use App\Models\Banco;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\HistoricoContabil;
use App\Models\JobProgress;
use App\Models\Layout;
use App\Models\LayoutColumn;
use App\Models\LayoutRule;
use App\Models\ParametroGeral;
use App\Models\PlanoDeConta;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class LayoutLancamentoResolverService
{
    private Layout $layout;
    private int $issuerId;
    private int $userId;
    private string $jobProgressId;

    /** @var \Illuminate\Support\Collection<int, LayoutRule> */
    private Collection $rules;

    /** @var \Illuminate\Support\Collection<int, LayoutColumn> */
    private Collection $layoutColumns;

    /** @var \Illuminate\Support\Collection<int, ParametroGeral> */
    private Collection $parametros;

    /** @var array<int, string> */
    private array $historicosByCodigo = [];

    public function __construct(Layout $layout, int $issuerId, int $userId, string $jobProgressId)
    {
        $this->layout = $layout;
        $this->issuerId = $issuerId;
        $this->userId = $userId;
        $this->jobProgressId = $jobProgressId;

        $this->rules = $layout->layoutRules->sortBy('position')->values();
        $this->layoutColumns = $layout->layoutColumns->sortBy('id')->values();

        $this->parametros = ParametroGeral::where('issuer_id', $issuerId)
            ->orderBy('order')
            ->get();

        $this->historicosByCodigo = HistoricoContabil::where('issuer_id', $issuerId)
            ->get()
            ->mapWithKeys(fn($h) => [$h->codigo => $h->descricao])
            ->toArray();
    }

    /**
     * Resolve todas as linhas do Excel em lançamentos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolveRows(array $rows): array
    {
        $result = [];
        $jobProgress = $this->jobProgressId ? JobProgress::find($this->jobProgressId) : null;

        $totalRows = count($rows);

        foreach ($rows as $index => $row) {

            $rowNumber = $index + 1;

            // Atualiza o progresso a cada 10 linhas ou no final
            if ($jobProgress && ($rowNumber % 10 === 0 || $rowNumber === $totalRows)) {
                $percentage = 20 + (int) (($rowNumber / $totalRows) * 70); // 20% a 90%
                $jobProgress->update([
                    'progress' => $percentage,
                    'message' => "Processando linha {$rowNumber} de {$totalRows}...",
                ]);                
            }

            $result[] = $this->resolveRow($row);
        }
        return $result;
    }

    /**
     * Resolve uma única linha do Excel.
     *
     * @return array<string, mixed>
     */
    public function resolveRow(array $row): array
    {
        $resolved = [
            'data' => null,
            'data_formatada' => null,
            'debito' => null,
            'credito' => null,
            'valor' => null,
            'valor_formatado' => null,
            'historico' => ' ',
            'debito_descricao' => null,
            'credito_descricao' => null,
            'cod_historico' => null,
            'col_historico' => null,
        ];

        $historicoTemplate = null;
        $metadata = [
            'row' => $row,
            'rule_trace' => [],
        ];

        foreach ($this->rules as $rule) {
            if (!$this->conditionPasses($rule, $row)) {
                continue;
            }

            $valuePayload = $this->resolveRuleValue($rule, $row);
            $value = $valuePayload['value'];
            $descricao = $valuePayload['descricao'];
            $codHistorico = $valuePayload['cod_historico'];
            $historicoFromParam = $valuePayload['historico_template'];
            $dateFormat = $valuePayload['date_format'];

            if ($rule->data_source_historico) {
                $historicoTemplate = $this->resolveHistoricoByCodigo((int) $rule->data_source_historico);
                $resolved['cod_historico'] = (int) $rule->data_source_historico;
            }

            if ($historicoFromParam) {
                $historicoTemplate = $historicoFromParam;
                $resolved['cod_historico'] = $codHistorico ?? $resolved['cod_historico'];
            }

            switch ($rule->rule_type?->value ?? $rule->rule_type) {
                case 'data_da_operacao':
                    $date = $value instanceof Carbon ? $value : $this->parseExcelDate($value, $rule->data_format);
                    $resolved['data'] = $this->applyDateAdjustment($date, (string) $rule->date_adjustment);
                    $resolved['data_formatada'] = $this->formatDate($resolved['data'], $dateFormat);
                    break;
                case 'valor_da_operacao':
                    $resolved['valor'] = $valuePayload['numeric_value'];
                    $resolved['valor_formatado'] = $valuePayload['formatted_value'];
                    break;
                case 'operacao_de_debito':
                    $resolved['debito'] = $value;
                    $resolved['debito_descricao'] = $descricao ?? $resolved['debito_descricao'];
                    break;
                case 'operacao_de_credito':
                    $resolved['credito'] = $value;
                    $resolved['credito_descricao'] = $descricao ?? $resolved['credito_descricao'];
                    break;
                case 'historico_contabil':
                    $historicoTemplate = (string) $value;
                    break;
                default:
                    break;
            }

            $metadata['rule_trace'][] = [
                'rule_id' => $rule->id,
                'rule_type' => $rule->rule_type,
                'value' => $value,
            ];
        }

        if ($historicoTemplate) {
            $resolved['historico'] = $this->resolveHistoricoTemplate($historicoTemplate, $row, $resolved);
        }

        $resolved['metadata'] = $metadata;

        return $resolved;
    }

    private function resolveRuleValue(LayoutRule $rule, array $row): array
    {
        $value = null;
        $descricao = null;
        $codHistorico = null;
        $historicoTemplate = null;
        $numericValue = null;
        $formattedValue = null;
        $dateFormat = null;

        switch ($rule->data_source_type?->value ?? $rule->data_source_type) {
            case 'column':
                $value = $this->getRowValue($row, (string) $rule->data_source);
                $column = $this->getLayoutColumnByExcelName((string) $rule->data_source);
                if ($column) {
                    $dateFormat = $column->format ?: null;
                    if ($column->data_type === 'date') {
                        $value = $this->parseExcelDate($value, $column->format);
                    } elseif ($column->data_type === 'number') {
                        $parsed = $this->parseNumberWithFormat($value, $column->format);
                        $numericValue = $parsed['numeric'];
                        $formattedValue = $parsed['formatted'];
                        $value = $numericValue;
                    }
                }
                break;
            case 'constant':
                $value = $rule->data_source_constant;
                break;
            case 'query':
                $result = $this->resolveQuery($rule, $row);
                $value = $result['value'];
                $descricao = $result['descricao'];
                break;
            case 'parametros_gerais':
                $match = $this->matchParametro($row);
                if ($match) {
                    $value = $match['conta_contabil'];
                    $descricao = $match['conta_contabil_descricao'];
                    $codHistorico = $match['codigo_historico'];
                    $historicoTemplate = $match['historico_template'];
                }
                break;
            default:
                $value = null;
        }

        if ($value === null || $value === '') {
            $value = $rule->default_value ?? ' ';
        }

        return [
            'value' => $value,
            'descricao' => $descricao,
            'cod_historico' => $codHistorico,
            'historico_template' => $historicoTemplate,
            'numeric_value' => $numericValue,
            'formatted_value' => $formattedValue,
            'date_format' => $dateFormat,
        ];
    }

    private function formatDate(?Carbon $date, ?string $format): ?string
    {
        if (!$date) {
            return null;
        }
        if ($format) {
            return $date->format($format);
        }
        return $date->format('d/m/Y');
    }

    private function resolveQuery(LayoutRule $rule, array $row): array
    {
        $searchValue = null;
        if ($rule->data_source_value_type === 'column') {
            $searchValue = $this->getRowValue($row, (string) $rule->data_source_search_value);
        } elseif ($rule->data_source_value_type === 'constant') {
            $searchValue = $rule->data_source_search_constant;
        }

        $searchValue = $searchValue ?? '';

        $table = (string) $rule->data_source_table;
        $attribute = (string) $rule->data_source_attribute;
        $condition = (string) $rule->data_source_condition;

        if ($table === 'contabil_bancos') {
            $query = Banco::with('plano_de_conta')->where('issuer_id', $this->issuerId);
            if ($condition === 'like') {
                $query->where($attribute, 'LIKE', '%' . $searchValue . '%');
            } else {
                $query->where($attribute, $searchValue);
            }
            $banco = $query->first();
            $codigo = $banco?->plano_de_conta?->codigo ?? null;
            $descricao = $banco?->plano_de_conta?->nome ?? null;
            return ['value' => $codigo ?? $rule->default_value ?? ' ', 'descricao' => $descricao];
        }

        if ($table === 'contabil_clientes') {
            $query = Cliente::with('plano_de_conta')->where('issuer_id', $this->issuerId);
            if ($condition === 'like') {
                $query->where($attribute, 'LIKE', '%' . $searchValue . '%');
            } else {
                $query->where($attribute, $searchValue);
            }
            $cliente = $query->first();
            $codigo = $this->extractContaCodigo($cliente);
            $descricao = $this->extractContaDescricao($cliente);
            return ['value' => $codigo ?? $rule->default_value ?? ' ', 'descricao' => $descricao];
        }

        if ($table === 'contabil_fornecedores') {
            $query = Fornecedor::where('issuer_id', $this->issuerId);
            if ($condition === 'like') {
                $query->where($attribute, 'LIKE', '%' . $searchValue . '%');
            } else {
                $query->where($attribute, $searchValue);
            }
            $fornecedor = $query->first();
            $codigo = $this->extractContaCodigo($fornecedor);
            $descricao = $this->extractContaDescricao($fornecedor);
            return ['value' => $codigo ?? $rule->default_value ?? ' ', 'descricao' => $descricao];
        }

        if ($table === 'contabil_plano_de_contas') {
            $query = PlanoDeConta::where('issuer_id', $this->issuerId);
            if ($condition === 'like') {
                $query->where($attribute, 'LIKE', '%' . $searchValue . '%');
            } else {
                $query->where($attribute, $searchValue);
            }
            $plano = $query->first();
            $codigo = $plano?->codigo ?? null;
            $descricao = $plano?->nome ?? null;
            return ['value' => $codigo ?? $rule->default_value ?? ' ', 'descricao' => $descricao];
        }

        return ['value' => $rule->default_value ?? ' ', 'descricao' => null];
    }

    private function matchParametro(array $row): ?array
    {
        $texto = $this->normalizeAllColumns($row);
        $best = null;
        $bestScore = -1;
        $bestOrder = PHP_INT_MAX;

        foreach ($this->parametros as $parametro) {
            $terms = $parametro->params ?? [];
            if (empty($terms)) {
                continue;
            }

            $isInclusivo = (bool) $parametro->is_inclusivo;
            [$includeTerms, $excludeTerms] = $this->splitIncludeExcludeTerms($terms);
            if (empty($includeTerms) && empty($excludeTerms)) {
                continue;
            }

            if ($this->hasExcludedTerm($texto, $excludeTerms)) {
                continue;
            }

            $matches = 0;

            foreach ($includeTerms as $term) {
                $termNormalized = $this->normalizeText((string) $term);
                if ($termNormalized === '') {
                    continue;
                }
                if (str_contains($texto, $termNormalized)) {
                    $matches++;
                }
            }

            $matched = ($isInclusivo && $matches === count($includeTerms)) || (!$isInclusivo && $matches > 0);
            if ($matched) {
                $score = $matches;
                $order = is_numeric($parametro->order) ? (int) $parametro->order : PHP_INT_MAX;

                if ($score > $bestScore || ($score === $bestScore && $order < $bestOrder)) {
                    $bestScore = $score;
                    $bestOrder = $order;
                    $best = [
                        'conta_contabil' => Arr::get($parametro->descricao_conta_contabil, 'codigo'),
                        'conta_contabil_descricao' => Arr::get($parametro->descricao_conta_contabil, 'descricao'),
                        'codigo_historico' => $parametro->codigo_historico,
                        'historico_template' => Arr::get($parametro->descricao_historico, 'descricao'),
                    ];
                }
            }
        }

        return $best;
    }

    /**
     * Separa termos de inclusão e exclusão.
     *
     * Convenções aceitas para exclusão:
     * - Prefixo "!" ou "-"
     * - Prefixo "NOT ", "NAO " ou "NÃO "
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function splitIncludeExcludeTerms(array $terms): array
    {
        $include = [];
        $exclude = [];

        foreach ($terms as $term) {
            $raw = $this->normalizeText((string) $term);
            if ($raw === '') {
                continue;
            }

            $isExclude = false;
            $value = $raw;

            if (str_starts_with($value, '!') || str_starts_with($value, '-')) {
                $isExclude = true;
                $value = ltrim($value, '!- ');
            } elseif (str_starts_with($value, 'NOT ')) {
                $isExclude = true;
                $value = trim(substr($value, 4));
            } elseif (str_starts_with($value, 'NAO ')) {
                $isExclude = true;
                $value = trim(substr($value, 4));
            } elseif (str_starts_with($value, 'NÃO ')) {
                $isExclude = true;
                $value = trim(substr($value, 4));
            }

            if ($value === '') {
                continue;
            }

            if ($isExclude) {
                $exclude[] = $value;
            } else {
                $include[] = $value;
            }
        }

        return [$include, $exclude];
    }

    private function hasExcludedTerm(string $texto, array $excludeTerms): bool
    {
        if (empty($excludeTerms)) {
            return false;
        }

        foreach ($excludeTerms as $term) {
            $termNormalized = $this->normalizeText((string) $term);
            if ($termNormalized === '') {
                continue;
            }
            if (str_contains($texto, $termNormalized)) {
                return true;
            }
        }

        return false;
    }

    private function conditionPasses(LayoutRule $rule, array $row): bool
    {
        if (($rule->condition_type ?? 'none') !== 'if') {
            return true;
        }

        $left = null;
        if ($rule->condition_data_source_type === 'column') {
            $left = $this->getRowValue($row, (string) $rule->condition_data_source);
        } elseif ($rule->condition_data_source_type === 'constant') {
            $left = $rule->condition_data_source_constant;
        }

        $left = $this->normalizeText((string) ($left ?? ''));
        $right = $this->normalizeText((string) ($rule->condition_value ?? ''));

        return match ($rule->condition_operator) {
            '=' => $left === $right,
            '!=' => $left !== $right,
            '>' => $left > $right,
            '<' => $left < $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            'contains' => str_contains($left, $right),
            'not_contains' => !str_contains($left, $right),
            'empty' => $left === '',
            'not_empty' => $left !== '',
            default => true,
        };
    }

    private function normalizeAllColumns(array $row): string
    {
        $parts = [];
        foreach ($this->layoutColumns as $column) {
            $key = $column->excel_column_name;
            $value = $this->getRowValue($row, (string) $key);
            if ($key === 'CNPJ / CPF') {
                $parts[] = sanitize($this->stringifyValue($value)) ?? '';
            } else {
                $parts[] = $this->normalizeText($this->stringifyValue($value));
            }
        }
        return trim(implode(' ', $parts));
    }

    private function normalizeText(string $value): string
    {
        $value = mb_strtoupper($value, 'UTF-8');
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }

    private function getRowValue(array $row, string $excelColumnName)
    {
        $map = $this->buildRowKeyMap($row);
        $needle = $this->normalizeHeader($excelColumnName);
        $key = $map[$needle] ?? null;
        $value = $key ? ($row[$key] ?? null) : null;

        if ($excelColumnName === 'CNPJ / CPF') {
            return sanitize($this->stringifyValue($value));
        }

        return $value;
    }

    private function stringifyValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private function buildRowKeyMap(array $row): array
    {
        $map = [];
        foreach (array_keys($row) as $key) {
            $map[$this->normalizeHeader($key)] = $key;
        }
        return $map;
    }

    private function normalizeHeader(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }

    private function getLayoutColumnByExcelName(string $excelColumnName): ?LayoutColumn
    {
        return $this->layoutColumns->first(function (LayoutColumn $col) use ($excelColumnName) {
            return $this->normalizeHeader($col->excel_column_name) === $this->normalizeHeader($excelColumnName);
        });
    }

    private function parseExcelDate($value, ?string $format): ?Carbon
    {

        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value);
            }

            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            }

            $value = trim((string) $value);
            foreach (['d/m/Y', 'd/m/y'] as $f) {
                $dt = Carbon::createFromFormat($f, $value);
                if ($dt !== false) {
                    return $dt;
                }
            }

            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseNumberWithFormat($value, ?string $format): array
    {
        if ($value === null || $value === '') {
            return ['numeric' => null, 'formatted' => null];
        }

        $raw = (string) $value;

        // Remove espaços
        $raw = str_replace(' ', '', $raw);

        // Detecta se já está no formato brasileiro (vírgula como decimal)
        $hasBrazilianFormat = str_contains($raw, ',') && !str_contains($raw, '.');

        // Se está no formato brasileiro, converte para formato padrão (ponto como decimal)
        if ($hasBrazilianFormat) {
            $raw = str_replace(',', '.', $raw);
        }
        // Se tem tanto vírgula quanto ponto, assume formato europeu (ponto como separador de milhares)
        elseif (str_contains($raw, ',') && str_contains($raw, '.')) {
            // Remove pontos (separadores de milhares) e converte vírgula para ponto decimal
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        }
        // Se tem apenas pontos, verifica se é separador decimal ou de milhares
        elseif (str_contains($raw, '.')) {
            // Se há mais de um ponto ou o último ponto não está nas últimas 3 posições,
            // trata como separador de milhares
            $lastDotPos = strrpos($raw, '.');
            $dotCount = substr_count($raw, '.');

            if ($dotCount > 1 || (strlen($raw) - $lastDotPos - 1) > 3) {
                // Remove todos os pontos (separadores de milhares)
                $raw = str_replace('.', '', $raw);
            }
            // Caso contrário, mantém como separador decimal
        }

        $num = is_numeric($raw) ? (float) $raw : null;
        if ($num === null) {
            return ['numeric' => null, 'formatted' => null];
        }

        $decimals = 2;
        $decimalSeparator = ',';
        if ($format && preg_match('/^(\d+)([.,])$/', $format, $m)) {
            $decimals = (int) $m[1];
            $decimalSeparator = $m[2];
        }

        return [
            'numeric' => $num,
            'formatted' => number_format($num, $decimals, $decimalSeparator, ''),
        ];
    }

    private function applyDateAdjustment(?Carbon $date, string $adjustment): ?Carbon
    {
        if (!$date) {
            return null;
        }

        return match ($adjustment) {
            'd-1' => $this->addBusinessDays($date, -1),
            'd+1' => $this->addBusinessDays($date, 1),
            default => $date,
        };
    }

    private function addBusinessDays(Carbon $date, int $value): Carbon
    {
        if ($value === 0) {
            return $date;
        }

        $current = clone $date;
        $increment = $value > 0 ? 1 : -1;
        $remaining = abs($value);

        while ($remaining > 0) {
            $current->addDays($increment);
            if ($current->isWeekday()) {
                $remaining--;
            }
        }

        return $current;
    }

    private function resolveHistoricoByCodigo(int $codigo): string
    {
        return $this->historicosByCodigo[$codigo] ?? ' ';
    }

    private function resolveHistoricoTemplate(string $template, array $row, array $resolved): string
    {
        $replacements = [];

        foreach ($this->layoutColumns as $col) {
            $key = $col->excel_column_name;
            $replacements['#' . $key] = $this->stringifyValue($this->getRowValue($row, $key) ?? ' ');
        }

        $date = $resolved['data'] instanceof Carbon ? $resolved['data'] : null;
        $valorFormatado = $resolved['valor_formatado'] ?? ' ';

        $replacements['#M'] = $date ? $date->format('d/m/Y') : ' ';
        $replacements['#N'] = $date ? $date->format('m/Y') : ' ';
        $replacements['#A'] = $date ? $date->copy()->subMonthNoOverflow()->format('m/Y') : ' ';
        $replacements['#D'] = $resolved['debito_descricao'] ?? ' ';
        $replacements['#C'] = $resolved['credito_descricao'] ?? ' ';
        $replacements['#V'] = $valorFormatado ?? ' ';

        return strtr($template, $replacements);
    }

    private function extractContaCodigo($model): ?string
    {
        if (!$model) {
            return null;
        }

        if (is_array($model->descricao_conta_contabil) && isset($model->descricao_conta_contabil['codigo'])) {
            return $model->descricao_conta_contabil['codigo'];
        }

        if (is_array($model->conta_contabil) && isset($model->conta_contabil['codigo'])) {
            return $model->conta_contabil['codigo'];
        }

        if (is_numeric($model->conta_contabil)) {
            $plano = PlanoDeConta::where('issuer_id', $this->issuerId)
                ->where('id', $model->conta_contabil)
                ->first();
            return $plano?->codigo;
        }

        return null;
    }

    private function extractContaDescricao($model): ?string
    {
        if (!$model) {
            return null;
        }

        if (is_array($model->descricao_conta_contabil) && isset($model->descricao_conta_contabil['descricao'])) {
            return $model->descricao_conta_contabil['descricao'];
        }

        if (is_numeric($model->conta_contabil)) {
            $plano = PlanoDeConta::where('issuer_id', $this->issuerId)
                ->where('id', $model->conta_contabil)
                ->first();
            return $plano?->nome;
        }

        return null;
    }
}