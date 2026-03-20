<?php

namespace App\Models;

use App\Enums\ManutencaoFrequenciaEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManutencaoRecorrencia extends Model
{
    protected $table = 'manutencao_recorrencias';

    protected $guarded = ['id'];

    protected $casts = [
        'frequencia' => ManutencaoFrequenciaEnum::class,
        'dia_mes' => 'integer',
        'dia_semana' => 'integer',
        'mes' => 'integer',
        'intervalo' => 'integer',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'gerar_dias_antecedencia' => 'integer',
        'ativo' => 'boolean',
        'ultima_geracao' => 'date',
        'proxima_geracao' => 'date',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function tipoManutencao(): BelongsTo
    {
        return $this->belongsTo(TipoManutencao::class);
    }

    public function manutencoes(): HasMany
    {
        return $this->hasMany(Manutencao::class, 'recorrencia_id');
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeProntas($query)
    {
        return $query->where('ativo', true)
            ->where('proxima_geracao', '<=', now()->toDateString());
    }

    public function scopePorFrequencia($query, ManutencaoFrequenciaEnum $frequencia)
    {
        return $query->where('frequencia', $frequencia);
    }

    // Métodos auxiliares
    public function calcularProximaGeracao(): Carbon
    {
        $ultimaGeracao = $this->ultima_geracao ? Carbon::parse($this->ultima_geracao) : Carbon::parse($this->data_inicio);

        return match ($this->frequencia) {
            ManutencaoFrequenciaEnum::DIARIA => $ultimaGeracao->addDays($this->intervalo),
            ManutencaoFrequenciaEnum::SEMANAL => $ultimaGeracao->addWeeks($this->intervalo),
            ManutencaoFrequenciaEnum::QUINZENAL => $ultimaGeracao->addWeeks(2 * $this->intervalo),
            ManutencaoFrequenciaEnum::MENSAL => $ultimaGeracao->addMonths($this->intervalo),
            ManutencaoFrequenciaEnum::BIMESTRAL => $ultimaGeracao->addMonths(2 * $this->intervalo),
            ManutencaoFrequenciaEnum::TRIMESTRAL => $ultimaGeracao->addMonths(3 * $this->intervalo),
            ManutencaoFrequenciaEnum::SEMESTRAL => $ultimaGeracao->addMonths(6 * $this->intervalo),
            ManutencaoFrequenciaEnum::ANUAL => $ultimaGeracao->addYears($this->intervalo),
        };
    }

    public function calcularDataManutencao(): Carbon
    {
        $proximaGeracao = $this->calcularProximaGeracao();

        // Ajustar para dia específico se configurado
        if ($this->dia_mes && in_array($this->frequencia, [
            ManutencaoFrequenciaEnum::MENSAL,
            ManutencaoFrequenciaEnum::BIMESTRAL,
            ManutencaoFrequenciaEnum::TRIMESTRAL,
            ManutencaoFrequenciaEnum::SEMESTRAL,
            ManutencaoFrequenciaEnum::ANUAL
        ])) {
            $proximaGeracao->day = min($this->dia_mes, $proximaGeracao->daysInMonth);
        }

        // Ajustar para dia da semana se configurado
        if ($this->dia_semana !== null && in_array($this->frequencia, [
            ManutencaoFrequenciaEnum::SEMANAL,
            ManutencaoFrequenciaEnum::QUINZENAL
        ])) {
            $proximaGeracao = $proximaGeracao->next($this->dia_semana);
        }

        // Ajustar para mês específico se anual
        if ($this->mes && $this->frequencia === ManutencaoFrequenciaEnum::ANUAL) {
            $proximaGeracao->month = $this->mes;
        }

        return $proximaGeracao;
    }

    public function podeGerar(): bool
    {
        if (!$this->ativo) {
            return false;
        }

        if ($this->data_fim && now() > $this->data_fim) {
            return false;
        }

        if (!$this->proxima_geracao) {
            return true;
        }

        return now()->toDateString() >= $this->proxima_geracao;
    }

    public function gerarManutencao(): Manutencao
    {
        $dataManutencao = $this->calcularDataManutencao();

        $manutencao = Manutencao::create([
            'issuer_id' => $this->issuer_id,
            'tipo_manutencao_id' => $this->tipo_manutencao_id,
            'recorrencia_id' => $this->id,
            'titulo' => $this->processarTemplate($this->titulo_template, $dataManutencao),
            'descricao' => $this->processarTemplate($this->descricao_template, $dataManutencao),
            'tipo' => $this->tipoManutencao->categoria->value === 'preventiva' ? 'preventiva' : 'corretiva',
            'status' => 'programada',
            'prioridade' => $this->tipoManutencao->prioridade->value,
            'data_programada' => $dataManutencao->toDateString(),
        ]);

        // Atualizar dados da recorrência
        $this->update([
            'ultima_geracao' => now()->toDateString(),
            'proxima_geracao' => $this->calcularProximaGeracao()->toDateString(),
        ]);

        return $manutencao;
    }

    private function processarTemplate(?string $template, Carbon $data): ?string
    {
        if (!$template) {
            return null;
        }

        $replacements = [
            '{data}' => $data->format('d/m/Y'),
            '{mes}' => $data->format('m/Y'),
            '{ano}' => $data->format('Y'),
            '{tipo}' => $this->tipoManutencao->nome,
            '{frequencia}' => $this->frequencia->getLabel(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public function getProximasGeracoes(int $quantidade = 5): array
    {
        $geracoes = [];
        $data = $this->calcularProximaGeracao();

        for ($i = 0; $i < $quantidade; $i++) {
            $geracoes[] = $data->copy();
            $data = $this->calcularProximaGeracao();
        }

        return $geracoes;
    }
}
