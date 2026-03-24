<?php

namespace App\Models;

use App\Enums\IssuerControlFrequencyEnum;
use App\Models\Issuer;
use App\Models\IssuerControl;
use App\Models\IssuerControlType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class IssuerControlRecorrency extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'frequencia' => IssuerControlFrequencyEnum::class,
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

    public function typeControl(): BelongsTo
    {
        return $this->belongsTo(IssuerControlType::class);
    }

    public function controls(): HasMany
    {
        return $this->hasMany(IssuerControl::class, 'recorrencia_id');
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

    public function scopePorFrequencia($query, IssuerControlFrequencyEnum $frequencia)
    {
        return $query->where('frequencia', $frequencia);
    }

    // Métodos auxiliares
    public function calcularProximaGeracao(): Carbon
    {
        $ultimaGeracao = $this->ultima_geracao ? Carbon::parse($this->ultima_geracao) : Carbon::parse($this->data_inicio);

        return match ($this->frequencia) {
            IssuerControlFrequencyEnum::DIARIA => $ultimaGeracao->addDays($this->intervalo),
            IssuerControlFrequencyEnum::SEMANAL => $ultimaGeracao->addWeeks($this->intervalo),
            IssuerControlFrequencyEnum::QUINZENAL => $ultimaGeracao->addWeeks(2 * $this->intervalo),
            IssuerControlFrequencyEnum::MENSAL => $ultimaGeracao->addMonths($this->intervalo),
            IssuerControlFrequencyEnum::BIMESTRAL => $ultimaGeracao->addMonths(2 * $this->intervalo),
            IssuerControlFrequencyEnum::TRIMESTRAL => $ultimaGeracao->addMonths(3 * $this->intervalo),
            IssuerControlFrequencyEnum::SEMESTRAL => $ultimaGeracao->addMonths(6 * $this->intervalo),
            IssuerControlFrequencyEnum::ANUAL => $ultimaGeracao->addYears($this->intervalo),
        };
    }

    public function calcularDataControle(): Carbon
    {
        $proximaGeracao = $this->calcularProximaGeracao();

        // Ajustar para dia específico se configurado
        if ($this->dia_mes && in_array($this->frequencia, [
            IssuerControlFrequencyEnum::MENSAL,
            IssuerControlFrequencyEnum::BIMESTRAL,
            IssuerControlFrequencyEnum::TRIMESTRAL,
            IssuerControlFrequencyEnum::SEMESTRAL,
            IssuerControlFrequencyEnum::ANUAL
        ])) {
            $proximaGeracao->day = min($this->dia_mes, $proximaGeracao->daysInMonth);
        }

        // Ajustar para dia da semana se configurado
        if ($this->dia_semana !== null && in_array($this->frequencia, [
            IssuerControlFrequencyEnum::SEMANAL,
            IssuerControlFrequencyEnum::QUINZENAL
        ])) {
            $proximaGeracao = $proximaGeracao->next($this->dia_semana);
        }

        // Ajustar para mês específico se anual
        if ($this->mes && $this->frequencia === IssuerControlFrequencyEnum::ANUAL) {
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

    public function gerarControle(): IssuerControl
    {
        $dataControle = $this->calcularDataControle();

        $controle = IssuerControl::create([
            'issuer_id' => $this->issuer_id,
            'type_control_id' => $this->type_control_id,
            'recorrencia_id' => $this->id,
            'titulo' => $this->processarTemplate($this->titulo_template, $dataControle),
            'descricao' => $this->processarTemplate($this->descricao_template, $dataControle),
            'tipo' => $this->typeControl->categoria->value === 'preventiva' ? 'preventiva' : 'corretiva',
            'status' => 'programada',
            'prioridade' => $this->typeControl->prioridade->value,
            'data_programada' => $dataControle->toDateString(),
        ]);

        // Atualizar dados da recorrência
        $this->update([
            'ultima_geracao' => now()->toDateString(),
            'proxima_geracao' => $this->calcularProximaGeracao()->toDateString(),
        ]);

        return $controle;
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
