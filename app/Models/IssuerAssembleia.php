<?php

namespace App\Models;

use App\Enums\AssembleiaStatusEnum;
use App\Enums\AtaStatusEnum;
use App\Enums\DeliberacaoStatusEnum;
use App\Enums\IssuerAgeTypeEnum;
use App\Enums\IssuerAssembleiaPrazoTecnicoEnum;
use App\Models\IssuerAssembleiaEventLog;
use App\Observers\IssuerAssembleiaObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([IssuerAssembleiaObserver::class])]
class IssuerAssembleia extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'vigencia_date' => 'date',
        'data_limite_edital' => 'date',
        'data_limite_ago' => 'date',
        'mandato_fim' => 'date',
        'mandato_conselho_fim' => 'date',
        'mandato_banco_fim' => 'date',
        'data_realizacao' => 'date',
        'tem_isencao_remuneracao' => 'array',
        'tem_isencao' => 'boolean',
        'tem_remuneracao' => 'boolean',
        'quem_recebe_isencao' => 'array',
        'quem_recebe_remuneracao' => 'array',
        'valor_isencao' => 'decimal:2',
        'valor_remuneracao' => 'decimal:2',
        'type' => IssuerAgeTypeEnum::class,
        'assembleia_status' => AssembleiaStatusEnum::class,
        'ata_status' => AtaStatusEnum::class,
        'deliberacao_status' => DeliberacaoStatusEnum::class,
    ];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function prazoTecnicoStatus(?Carbon $baseDate = null): ?IssuerAssembleiaPrazoTecnicoEnum
    {
        $periodo = $this->prazoTecnicoPeriodoAtual($baseDate);

        if ($periodo === null) {
            return null;
        }

        return $periodo['status'];
    }

    public function prazoTecnicoPeriodoAtual(?Carbon $baseDate = null): ?array
    {
        $deadline = $this->data_limite_edital?->copy()->startOfDay();
        $prazoTecnicoDias = $this->prazo_tecnico_edital ?? $this->prazo_tecnico;
        $numDayControl = $this->num_day_control;

        if (! $deadline || ! $prazoTecnicoDias || ! $numDayControl) {
            return null;
        }

        $today = ($baseDate ?? now())->startOfDay();

        if ($today->gt($deadline)) {
            return [
                'status' => IssuerAssembleiaPrazoTecnicoEnum::ATRASADO,
                'inicio' => null,
                'fim' => null,
                'faixa' => null,
            ];
        }

        $inicioPrazo = $deadline->copy()->subDays($prazoTecnicoDias);

        if ($today->lt($inicioPrazo)) {
            return [
                'status' => IssuerAssembleiaPrazoTecnicoEnum::ANTES_DO_PRAZO,
                'inicio' => null,
                'fim' => null,
                'faixa' => null,
            ];
        }

        $diasDesdeInicio = $inicioPrazo->diffInDays($today);
        $faixa = intdiv($diasDesdeInicio, $numDayControl) + 1;
        $maxFaixas = (int) ceil($prazoTecnicoDias / $numDayControl);
        $faixa = min($faixa, $maxFaixas);

        $faixaInicio = $inicioPrazo->copy()->addDays(($faixa - 1) * $numDayControl);
        $faixaFim = $faixaInicio->copy()->addDays($numDayControl - 1);
        if ($faixaFim->gt($deadline)) {
            $faixaFim = $deadline->copy();
        }

        return [
            'status' => IssuerAssembleiaPrazoTecnicoEnum::fromIndex($faixa),
            'inicio' => $faixaInicio,
            'fim' => $faixaFim,
            'faixa' => $faixa,
        ];
    }

    public function getPrazoTecnicoStatusAttribute(): ?IssuerAssembleiaPrazoTecnicoEnum
    {
        return $this->prazoTecnicoStatus();
    }

    public function getPrazoTecnicoPeriodoAttribute(): ?array
    {
        return $this->prazoTecnicoPeriodoAtual();
    }


    public function logs(): HasMany
    {
        return $this->hasMany(IssuerAssembleiaEventLog::class);
    }
}
