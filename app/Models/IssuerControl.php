<?php

namespace App\Models;

use App\Enums\IssuerControlFrequencyEnum;
use App\Enums\IssuerControlPriorityEnum;
use App\Enums\IssuerControlStatusEnum;
use App\Enums\IssuerControlTypeEnum;
use App\Models\Issuer;
use App\Observers\IssuerControlObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[ObservedBy([IssuerControlObserver::class])]
class IssuerControl extends Model
{
    protected $guarded = ['id'];

    protected $with = ['logs'];

    protected $casts = [
        'tipo' => IssuerControlTypeEnum::class,
        'periodicidade_padrao' => IssuerControlFrequencyEnum::class,
        'prioridade' => IssuerControlPriorityEnum::class,
        'status' => IssuerControlStatusEnum::class,
        'data_programada' => 'date',
        'data_execucao' => 'datetime',
        'data_conclusao' => 'datetime',
        'custo_estimado' => 'decimal:2',
        'custo_real' => 'decimal:2',
        'anexos' => 'array',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function typeControl(): BelongsTo
    {
        return $this->belongsTo(IssuerControlType::class);
    }

    public function recorrencia(): BelongsTo
    {
        return $this->belongsTo(IssuerControlRecorrency::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IssuerControlEventLog::class);
    }



    // Scopes
    public function scopePorStatus($query, IssuerControlStatusEnum $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorPrioridade($query, IssuerControlPriorityEnum $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }

    public function scopePorTipo($query, IssuerControlTypeEnum $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeAtrasadas($query)
    {
        return $query->where('data_programada', '<', now())
            ->whereIn('status', [IssuerControlStatusEnum::PROGRAMADA, IssuerControlStatusEnum::EM_ANDAMENTO]);
    }

    public function scopeProximas($query, int $dias = 7)
    {
        return $query->where('data_programada', '<=', now()->addDays($dias))
            ->where('data_programada', '>=', now())
            ->where('status', IssuerControlStatusEnum::PROGRAMADA);
    }

    public function scopeAtivas($query)
    {
        return $query->whereIn('status', [IssuerControlStatusEnum::PROGRAMADA, IssuerControlStatusEnum::EM_ANDAMENTO]);
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', IssuerControlStatusEnum::CONCLUIDA);
    }

    // Métodos auxiliares
    public function isAtrasada(): bool
    {
        return $this->data_programada < now()->toDateString() &&
            in_array($this->status, [IssuerControlStatusEnum::PROGRAMADA, IssuerControlStatusEnum::EM_ANDAMENTO]);
    }

    public function isProxima(int $dias = 7): bool
    {
        return $this->data_programada <= now()->addDays($dias)->toDateString() &&
            $this->data_programada >= now()->toDateString() &&
            $this->status === IssuerControlStatusEnum::PROGRAMADA;
    }

    public function getDuracaoAttribute(): ?int
    {
        if ($this->data_execucao && $this->data_conclusao) {
            return $this->data_execucao->diffInMinutes($this->data_conclusao);
        }

        return null;
    }

    public function getVariacaoCustoAttribute(): ?float
    {
        if ($this->custo_estimado && $this->custo_real) {
            return (($this->custo_real - $this->custo_estimado) / $this->custo_estimado) * 100;
        }

        return null;
    }

    public function getDiasAtrasoAttribute(): int
    {
        if ($this->isAtrasada()) {
            return Carbon::parse($this->data_programada)->diffInDays(now());
        }

        return 0;
    }

    public function podeIniciar(): bool
    {
        return $this->status === IssuerControlStatusEnum::PROGRAMADA;
    }

    public function podeConcluir(): bool
    {
        return $this->status === IssuerControlStatusEnum::EM_ANDAMENTO;
    }

    public function podeCancelar(): bool
    {
        return in_array($this->status, [IssuerControlStatusEnum::PROGRAMADA, IssuerControlStatusEnum::EM_ANDAMENTO]);
    }

    public function podeReagendar(): bool
    {
        return in_array($this->status, [IssuerControlStatusEnum::PROGRAMADA, IssuerControlStatusEnum::ATRASADA]);
    }
}
