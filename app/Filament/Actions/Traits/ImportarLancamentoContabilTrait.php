<?php

namespace App\Filament\Actions\Traits;

use App\Enums\TipoRegraExportacaoEnum;
use App\Models\HistoricoContabil;
use App\Models\ImportarLancamentoContabil;
use App\Models\Layout;
use App\Models\ParametroGeral;
use App\Models\PlanoDeConta;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait ImportarLancamentoContabilTrait
{
    private static function prepareData(array $dataFile, Layout $layout, $user = null, $jobProgress = null): Collection
    {
        $preparedData = new Collection;

        // Contadores e estrutura do relatório de processamento
        $processedCount = 0;
        $unprocessedCount = 0;
        $errorDetails = [];
        $warningDetails = [];

        $user_id = $user ? $user->id : Auth::user()->id;
        $issuer_id = $layout->issuer_id;
        // Ordena as regras pela posição
        $rules = $layout->layoutRules()->orderBy('position')->get();

        $totalRows = count($dataFile);

        foreach ($dataFile as $index => $row) {
            $rowNumber = $index + 1;

            // Atualiza o progresso a cada 10 linhas ou no final
            if ($jobProgress && ($rowNumber % 10 === 0 || $rowNumber === $totalRows)) {
                $percentage = 10 + (int) (($rowNumber / $totalRows) * 80); // Inicia em 10% e vai até 90%
                $jobProgress->update([
                    'progress' => $percentage,
                    'message' => "Processando linha {$rowNumber} de {$totalRows}...",
                ]);
            }

            $rowLine = [];
            $rowLine['operacao_de_debito'] = null;
            $rowLine['operacao_de_credito'] = null;
            $rowLine['valor_da_operacao'] = null;
            $rowLine['data_da_operacao'] = null;

            foreach ($rules as $rule) {

                $value = self::resolveRuleValue($rule, $row, $layout);

                if ($rule->rule_type === TipoRegraExportacaoEnum::DATA_DA_OPERACAO) {
                    $rowLine['data_da_operacao'] = is_null($value) ? $rowLine['data_da_operacao'] : $value;
                } elseif ($rule->rule_type === TipoRegraExportacaoEnum::OPERACAO_DE_DEBITO) {
                    $rowLine['operacao_de_debito'] = is_null($value) ? $rowLine['operacao_de_debito'] : $value;
                } elseif ($rule->rule_type === TipoRegraExportacaoEnum::OPERACAO_DE_CREDITO) {

                    $rowLine['operacao_de_credito'] = is_null($value) ? $rowLine['operacao_de_credito'] : $value;
                } elseif ($rule->rule_type === TipoRegraExportacaoEnum::VALOR_DA_OPERACAO) {
                    if ($rowLine['valor_da_operacao'] === null && $value !== null) {
                        $rowLine['valor_da_operacao'] = abs($value);
                    }
                }

                $rowLine['texto_linha'] = implode(' ', self::resolveHistoricoContabilValue($rule, $row, $layout));
            }

            if (! is_null($rowLine['valor_da_operacao'])) {

                $debito = isset($rowLine['operacao_de_debito']['conta_contabil_code']) ? $rowLine['operacao_de_debito']['conta_contabil_code'] : $rowLine['operacao_de_debito']['conta_contabil'] ?? null;
                $credito = isset($rowLine['operacao_de_credito']['conta_contabil_code']) ? $rowLine['operacao_de_credito']['conta_contabil_code'] : $rowLine['operacao_de_credito']['conta_contabil'] ?? null;

                $data = [
                    'issuer_id' => $issuer_id,
                    'user_id' => $user_id,
                    'data' => $rowLine['data_da_operacao'],
                    'valor' => $rowLine['valor_da_operacao'],
                    'debito' => $debito,
                    'credito' => $credito,
                    'is_exist' => ! is_null($rowLine['data_da_operacao'] ?? null) && ! is_null($rowLine['operacao_de_debito']['conta_contabil'] ?? null) && ! is_null($rowLine['operacao_de_credito']['conta_contabil'] ?? null),
                    'metadata' => [
                        'descricao_debito' => $debito !== null ? self::getDescricaoDebito($rowLine) : null,
                        'descricao_credito' => $credito !== null ? self::getDescricaoCredito($rowLine) : null,
                        'cod_historico' => $debito !== null && $credito !== null ? self::getCodigoHistorico($rowLine) : null,
                        'historico' => $debito !== null && $credito !== null ? self::getHistorico(self::getCodigoHistorico($rowLine), $issuer_id) : null,
                        'texto_linha' => $rowLine['texto_linha'].' ('.self::getCodigoHistorico($rowLine).')',
                        'row' => $row,
                    ],
                ];

                $import = new ImportarLancamentoContabil;
                $import->issuer_id = $issuer_id;
                $import->user_id = $user_id;
                $import->data = $rowLine['data_da_operacao'];
                $import->valor = $data['valor'];
                $import->debito = $data['debito'];
                $import->credito = $data['credito'];
                $import->is_exist = $data['is_exist'];
                $import->metadata = $data['metadata'];

                $import->historico = self::substituirCaracteresHistoricoContabil($import);

                $import->saveQuietly();

                $preparedData->push($rowLine);

                $processedCount++;
            } else {

                $unprocessedCount++;
                $debitoValor = isset($rowLine['operacao_de_debito']['conta_contabil_code']) ? $rowLine['operacao_de_debito']['conta_contabil_code'] : ($rowLine['operacao_de_debito']['conta_contabil'] ?? null);
                $creditoValor = isset($rowLine['operacao_de_credito']['conta_contabil_code']) ? $rowLine['operacao_de_credito']['conta_contabil_code'] : ($rowLine['operacao_de_credito']['conta_contabil'] ?? null);
                $errorDetails[] = [
                    'status' => 'nao_processada',
                    'linha' => $rowNumber + 1,
                    'identificador' => $rowLine['texto_linha'] ?? '',
                    'dados' => [
                        'data' => $rowLine['data_da_operacao'],
                        'valor' => $rowLine['valor_da_operacao'],
                        'debito' => $debitoValor,
                        'credito' => $creditoValor,
                    ],
                    'motivos' => ['Valor da operação ausente'],
                ];
            }
        }

        // Monta e salva relatório na sessão para exibição ao usuário
        $report = [
            'total_linhas' => count($dataFile),
            'linhas_processadas_com_sucesso' => $processedCount,
            'linhas_nao_processadas' => $unprocessedCount,
            'erros' => $errorDetails,
        ];

        // Armazena em sessão para que a Action/Controller possa exibir o relatório
        // Chave por emissor e por usuário, garantindo separação por usuário
        try {
            $sessionKey = sprintf('importar_lancamento_contabil.report.%s.%s', $issuer_id, $user_id);
            session()->put($sessionKey, $report);
        } catch (\Throwable $e) {
            Log::warning('Não foi possível salvar o relatório de importação na sessão: '.$e->getMessage());
        }

        // A exibição do relatório agora é feita em uma página dedicada do Filament,
        // evitando o uso de notificações estilo "toast".
        // O relatório está disponível em session("importar_lancamento_contabil.report.{issuer_id}.{user_id}").

        return $preparedData;
    }

    private static function resolveHistoricoContabilValue($rule, $row, $layout)
    {
        // Obtém todas as colunas do layout
        $targetColumns = $layout->layoutColumns->pluck('excel_column_name')->toArray();

        // Cria array com os valores das colunas, convertendo objetos DateTimeImmutable para string
        $searchValues = [];

        if ($targetColumns) {
            foreach ($targetColumns as $col) {
                $value = $row[$col] ?? null;

                // Converte DateTime para string no formato Y-m-d
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d');
                }

                // Se for array/objeto, converter para string (json) para exibição segura
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                // Retorna pares "coluna: valor" para que o texto do histórico contenha o nome da coluna junto do conteúdo
                $searchValues[] = $col.': '.(is_null($value) ? '' : (string) $value);
            }
        }

        return $searchValues;
    }

    private static function resolveRuleValue($rule, $row, $layout)
    {

        $value = match ($rule->data_source_type->value) {
            'column' => self::processColumnSource($rule, $row, $layout),
            'constant' => self::processConstantValue($rule),
            'parametros_gerais' => self::processParametrosGerais($rule, $row, $layout),
            'query' => self::processQuerySource($rule, $row),
            default => $rule->default_value ?? null
        };

        $result = self::applyCondition($rule, $row, $layout, $value);

        return $result;
    }

    private static function processColumnSource($rule, $row, $layout)
    {
        $column = $layout->layoutColumns()->where('excel_column_name', $rule->data_source)->first();

        $value = $row[$rule->data_source] ?? null;

        if (! isset($value) || ! $column) {
            return null;
        }

        return match ($column->data_type) {
            'number' => self::formatNumberValue($value, $column),
            'date' => self::formatDateValue($value, $layout, $rule),
            default => $value
        };
    }

    private static function formatNumberValue($value, $column)
    {

        if ($value < 0) {
            $value = $value * -1;
        }

        return (float) $value;
    }

    private static function formatDateValue($value, $layout, $rule)
    {
        try {
            $layoutColumn = $layout->layoutColumns->where('data_type', 'date')->first();
            $date = match (gettype($value)) {
                'integer' => Carbon::createFromFormat('d/m/y', $value)->format($layoutColumn->format),
                'string' => Carbon::createFromFormat('d/m/y', $value) ?? Carbon::createFromFormat('d/m/Y', $value),
                default => $value
            };

            if (! $date) {
                return Carbon::now();
            }

            return $date;
        } catch (Exception $e) {
            Log::error('Erro ao formatar a data: '.$e->getMessage());

            return null;
        }
    }

    private static function processConstantValue($rule)
    {

        $codigo = $rule->data_source_constant;
        $cacheKey = "plano_conta_{$codigo}";

        $planoDeConta = cache()->remember($cacheKey, now()->addHours(24), function () use ($codigo) {
            return PlanoDeConta::where('codigo', $codigo)->first();
        });

        if (! $planoDeConta) {
            return null;
        }

        return [
            'conta_contabil' => $planoDeConta->codigo,
            'nome' => $planoDeConta->nome,
            'codigo_historico' => $rule->data_source_historico ?? null,

        ];
    }

    private static function processParametrosGerais($rule, $row, $layout)
    {
        // Obtém todas as colunas do layout
        $targetColumns = $layout->layoutColumns->pluck('excel_column_name')->toArray();

        // Cria array com os valores das colunas, convertendo objetos DateTimeImmutable para string
        $searchValues = [];

        if ($targetColumns) {
            foreach ($targetColumns as $col) {
                $value = $row[$col] ?? null;

                // Converte DateTimeImmutable para string no formato Y-m-d
                if ($value instanceof \DateTimeImmutable || $value instanceof \DateTime) {
                    $value = $value->format('Y-m-d');
                }

                if ($value !== null) {
                    $searchValues[$col] = mb_strtoupper((string) $value, 'UTF-8');
                } else {
                    $searchValues[$col] = null;
                }
            }
        }

        // Busca todos os parâmetros cadastrados
        $params = ParametroGeral::where('issuer_id', $layout->issuer_id)->orderBy('order')->get();

        $parametroEncontrado = null;
        foreach ($params as $index => $parametro) {
            // Converte os termos do parâmetro para maiúsculo
            $termosOrig = is_array($parametro->params) ? $parametro->params : [];
            $termos = array_values(array_filter(array_map(fn ($termo) => mb_strtoupper((string) $termo, 'UTF-8'), $termosOrig), fn ($t) => $t !== ''));

            // Verifica se os termos estão presentes nos valores de busca
            $termosEncontrados = self::verificarTermos($termos, $searchValues, (bool) $parametro->is_inclusivo);

            if ($termosEncontrados) {
                $value = $parametro->toArray();
                $contaCodigo = $value['descricao_conta_contabil']['codigo'] ?? ($parametro->descricaoContaContabil->codigo ?? null);
                $value['conta_contabil'] = $contaCodigo;
                $parametroEncontrado = $value;
                break;
            }
        }

        return $parametroEncontrado;
    }

    /**
     * Verifica se os termos estão presentes nos valores de busca
     *
     * @param  array  $termos  Array de termos a serem buscados
     * @param  array  $searchValues  Array de valores onde buscar
     * @param  bool  $isInclusivo  Se true, todos os termos devem estar presentes
     */
    private static function verificarTermos(array $termos, array $searchValues, bool $isInclusivo): bool
    {

        if ($isInclusivo) {
            // Modo inclusivo: todos os termos devem estar presentes
            foreach ($termos as $termo) {
                $termoEncontrado = false;
                foreach ($searchValues as $valor) {
                    if (is_string($valor) && str_contains($valor, $termo)) {
                        $termoEncontrado = true;
                        break;
                    }
                }
                // Se qualquer termo não for encontrado, retorna false
                if (! $termoEncontrado) {
                    return false;
                }
            }

            return true;
        } else {
            // Modo OU: pelo menos um termo deve estar presente
            foreach ($termos as $termo) {
                foreach ($searchValues as $valor) {
                    if (is_string($valor) && str_contains($valor, $termo)) {
                        return true;
                    }
                }
            }

            return false;
        }
    }

    private static function processQuerySource($rule, $row)
    {
        try {

            $searchValue = match ($rule->data_source_value_type) {
                'constant' => $rule->data_source_search_constant,
                'column' => $row[$rule->data_source_search_value] ?? '',
                default => ''
            };

            if ($rule->is_sanitize) {
                $searchValue = sanitize($searchValue);
            }

            $query = sprintf(
                'SELECT * FROM %s WHERE %s %s ? AND issuer_id = '.$rule->layout->issuer_id.' LIMIT 1',
                $rule->data_source_table,
                $rule->data_source_attribute,
                $rule->data_source_condition
            );

            $searchValue = $rule->data_source_condition === 'like' ? "%$searchValue%" : $searchValue;

            if (is_string($searchValue) && trim($searchValue) !== '') {
                $result = DB::select($query, [$searchValue]);

                $data = isset($result[0]) ? (array) $result[0] : [];
                $data['codigo_historico'] = $rule->data_source_historico ?? null;
            }

            return $data ?? null;
        } catch (Exception $e) {
            Log::error('Erro ao executar a query: '.$e->getMessage());

            return $rule->default_value ?? null;
        }
    }

    private static function applyCondition($rule, $row, $layout, $value)
    {

        if ($rule->condition_type === 'none' || $value === null) {
            return $value;
        }

        if ($rule->condition_type === 'if') {
            $conditionValue = self::getConditionValue($rule, $row, $layout);

            $conditionResult = self::evaluateCondition($rule, trim($conditionValue));

            if (! $conditionResult) {

                if (isset($rule->default_value)) {
                    return $rule->default_value;
                }

                return null;
            }

            if ($rule->rule_type === TipoRegraExportacaoEnum::DATA_DA_OPERACAO) {

                return self::applyDateAdjustment($rule->date_adjustment, $value);
            }

            return $value;
        }

        return null;
    }

    /**
     * Aplica um ajuste de data baseado no tipo de ajuste especificado
     *
     * @param  string  $adjustmentType  Tipo de ajuste ('same', 'd-1', 'd+1')
     * @param  mixed  $value  Valor da data a ser ajustado
     * @return mixed Valor ajustado
     */
    private static function applyDateAdjustment($adjustmentType, $value)
    {

        switch ($adjustmentType) {
            case 'd-1':
                return self::getPreviousWorkingDay($value);
            case 'd+1':
                return self::getNextWorkingDay($value);
            case 'same':
            default:
                return $value;
        }
    }

    private static function getConditionValue($rule, $row, $layout)
    {
        return match ($rule->condition_data_source_type) {
            'column' => $row[$rule->condition_data_source] ?? null,
            'constant' => $rule->condition_data_source,
            'query' => self::executeConditionQuery($rule, $row, $layout),
            default => null
        };
    }

    private static function executeConditionQuery($rule, $row, $layout)
    {
        try {
            $conditionResult = DB::select($rule->condition_data_source, ['row' => $row, 'layout' => $layout]);

            return $conditionResult[0]->value ?? '';
        } catch (Exception $e) {
            Log::error('Erro ao executar a query da condição: '.$e->getMessage());

            return null;
        }
    }

    private static function evaluateCondition($rule, $conditionValue)
    {
        // Normaliza os valores para comparação
        $normalizeValue = function ($value) {
            if (is_null($value)) {
                return '';
            }

            return trim((string) $value);
        };

        $operator = $rule->condition_operator;
        $ruleValue = $rule->condition_value;

        // Normaliza os valores antes da comparação
        $normalizedConditionValue = $normalizeValue($conditionValue);
        $normalizedRuleValue = $normalizeValue($ruleValue);

        // Operadores com valores normalizados
        switch ($operator) {
            case '=':
                return $normalizedConditionValue == $normalizedRuleValue;
            case '!=':
                return $normalizedConditionValue != $normalizedRuleValue;
            case '>':
                return $normalizedConditionValue > $normalizedRuleValue;
            case '<':
                return $normalizedConditionValue < $normalizedRuleValue;
            case '>=':
                return $normalizedConditionValue >= $normalizedRuleValue;
            case '<=':
                return $normalizedConditionValue <= $normalizedRuleValue;
            case 'contains':
                return str_contains($normalizedConditionValue, $normalizedRuleValue);
            case 'not_contains':
                return ! str_contains($normalizedConditionValue, $normalizedRuleValue);
            case 'empty':
                return empty($conditionValue);
            case 'not_empty':
                return ! empty($conditionValue);
            default:
                return false;
        }
    }

    private static function getDescricaoDebito($row)
    {
        if (isset($row['operacao_de_debito']['nome'])) {
            return $row['operacao_de_debito']['nome'];
        }
        if (isset($row['operacao_de_debito']['descricao_conta_contabil']['descricao'])) {
            return $row['operacao_de_debito']['descricao_conta_contabil']['descricao'];
        }

        return null;
    }

    private static function getDescricaoCredito($row)
    {
        if (isset($row['operacao_de_credito']['nome'])) {
            return $row['operacao_de_credito']['nome'];
        }
        if (isset($row['operacao_de_credito']['descricao_conta_contabil']['descricao'])) {
            return $row['operacao_de_credito']['descricao_conta_contabil']['descricao'];
        }

        return null;
    }

    private static function getCodigoHistorico($row)
    {
        if (isset($row['operacao_de_credito']['codigo_historico'])) {
            return $row['operacao_de_credito']['codigo_historico'];
        }
        if (isset($row['operacao_de_debito']['codigo_historico'])) {
            return $row['operacao_de_debito']['codigo_historico'];
        }

        return null;
    }

    private static function getHistorico($codigo, $issuer_id)
    {
        return HistoricoContabil::where('issuer_id', $issuer_id)
            ->where('codigo', $codigo)
            ->first()?->descricao;
    }

    /**
     * Obtém o próximo dia útil
     *
     * @param  \DateTimeImmutable|\Carbon\Carbon  $date
     * @return \DateTimeImmutable|\Carbon\Carbon
     */
    private static function getNextWorkingDay($date)
    {
        if ($date instanceof \DateTimeImmutable) {
            $nextDay = $date->modify('+1 day');

            while (intval($nextDay->format('N')) >= 6) { // 6 = sábado, 7 = domingo
                $nextDay = $nextDay->modify('+1 day');
            }

            return $nextDay;
        } else {
            // Mantém compatibilidade com Carbon
            $nextDay = $date->copy()->addDay();

            while (! $nextDay->isWeekday()) {
                $nextDay->addDay();
            }

            return $nextDay;
        }
    }

    /**
     * Obtém o dia útil anterior
     *
     * @param  \DateTimeImmutable|\Carbon\Carbon  $date
     * @return \DateTimeImmutable|\Carbon\Carbon
     */
    private static function getPreviousWorkingDay($date)
    {
        if ($date instanceof \DateTimeImmutable) {
            $previousDay = $date->modify('-1 day');

            while (intval($previousDay->format('N')) >= 6) { // 6 = sábado, 7 = domingo
                $previousDay = $previousDay->modify('-1 day');
            }

            return $previousDay;
        } else {
            // Mantém compatibilidade com Carbon
            $previousDay = $date->copy()->subDay();

            while (! $previousDay->isWeekday()) {
                $previousDay->subDay();
            }

            return $previousDay;
        }
    }

    private static function substituirCaracteresHistoricoContabil($lancamento)
    {
        $texto = $lancamento->metadata['historico'];

        // Encontra todos os marcadores no formato #ALGO no texto
        preg_match_all('/#[^\s#]+/u', $texto, $matches);

        // Para cada marcador encontrado
        foreach ($matches[0] as $index => $marcador) {

            $codigo = substr($marcador, 1);  // Pega o código sem o #
            $valor = '';
            switch ($codigo) {

                case 'M':
                    $valor = $lancamento->data->format('d/m/Y');
                    break;

                case 'N':
                    $valor = $lancamento->data->format('m/Y');
                    break;

                case 'D':
                    $valor = $lancamento->metadata['descricao_debito'] ?? '';
                    break;

                case 'C':
                    $valor = $lancamento->metadata['descricao_credito'] ?? '';
                    break;

                case 'V':
                    $valor = number_format(abs($lancamento->valor), 2, ',', '');
                    break;

                case 'A':
                    $valor = $lancamento->data->copy()->subMonthNoOverflow()->format('m/Y');
                    break;

                default:
                    $codigo = strtr($codigo, '_', ' ');
                    // Verifica se é uma chave do array row
                    if (isset($lancamento->metadata['row'][$codigo])) {
                        $valor = $lancamento->metadata['row'][$codigo];

                        if (is_array($valor) && isset($valor['date'])) {
                            $valor = (string) Carbon::parse($valor['date'])->format('M/Y');
                        }
                    }
                    break;
            }
            // Substitui o marcador pelo valor no texto original

            $texto = str_replace($marcador, $valor, $texto);
        }

        // Retorna o texto com os marcadores substituídos
        return $texto;
    }
}
