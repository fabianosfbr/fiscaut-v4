<?php

namespace App\Models;

use App\Enums\ManutencaoPrioridadeEnum;
use App\Enums\ManutencaoStatusEnum;
use App\Enums\ManutencaoTipoEnum;
use App\Observers\ManutencaoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

#[ObservedBy([ManutencaoObserver::class])]
class Manutencao extends Model
{
    use SoftDeletes;

    protected $table = 'manutencoes';

    protected $with = ['historicos'];

    protected $guarded = ['id'];

    protected $casts = [
        'tipo' => ManutencaoTipoEnum::class,
        'status' => ManutencaoStatusEnum::class,
        'prioridade' => ManutencaoPrioridadeEnum::class,
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

    public function tipoManutencao(): BelongsTo
    {
        return $this->belongsTo(TipoManutencao::class);
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function recorrencia(): BelongsTo
    {
        return $this->belongsTo(ManutencaoRecorrencia::class);
    }

    public function usuarioResponsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_responsavel_id');
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(ManutencaoHistorico::class);
    }

    // Scopes
    public function scopePorStatus($query, ManutencaoStatusEnum $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorPrioridade($query, ManutencaoPrioridadeEnum $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }

    public function scopePorTipo($query, ManutencaoTipoEnum $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeAtrasadas($query)
    {
        return $query->where('data_programada', '<', now())
            ->whereIn('status', [ManutencaoStatusEnum::PROGRAMADA, ManutencaoStatusEnum::EM_ANDAMENTO]);
    }

    public function scopeProximas($query, int $dias = 7)
    {
        return $query->where('data_programada', '<=', now()->addDays($dias))
            ->where('data_programada', '>=', now())
            ->where('status', ManutencaoStatusEnum::PROGRAMADA);
    }

    public function scopeAtivas($query)
    {
        return $query->whereIn('status', [ManutencaoStatusEnum::PROGRAMADA, ManutencaoStatusEnum::EM_ANDAMENTO]);
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', ManutencaoStatusEnum::CONCLUIDA);
    }

    // Métodos auxiliares
    public function isAtrasada(): bool
    {
        return $this->data_programada < now()->toDateString() &&
            in_array($this->status, [ManutencaoStatusEnum::PROGRAMADA, ManutencaoStatusEnum::EM_ANDAMENTO]);
    }

    public function isProxima(int $dias = 7): bool
    {
        return $this->data_programada <= now()->addDays($dias)->toDateString() &&
            $this->data_programada >= now()->toDateString() &&
            $this->status === ManutencaoStatusEnum::PROGRAMADA;
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
        return $this->status === ManutencaoStatusEnum::PROGRAMADA;
    }

    public function podeConcluir(): bool
    {
        return $this->status === ManutencaoStatusEnum::EM_ANDAMENTO;
    }

    public function podeCancelar(): bool
    {
        return in_array($this->status, [ManutencaoStatusEnum::PROGRAMADA, ManutencaoStatusEnum::EM_ANDAMENTO]);
    }

    public function podeReagendar(): bool
    {
        return in_array($this->status, [ManutencaoStatusEnum::PROGRAMADA, ManutencaoStatusEnum::ATRASADA]);
    }
}
