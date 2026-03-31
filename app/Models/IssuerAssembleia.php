<?php

namespace App\Models;

use App\Enums\AssembleiaStatusEnum;
use App\Enums\AtaStatusEnum;
use App\Enums\DeliberacaoStatusEnum;
use App\Enums\IssuerAgeTypeEnum;
use App\Enums\IssuerAssembleiaPrazoTecnicoEnum;
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

    public function sindicoMandatoStatus(?Carbon $baseDate = null): ?IssuerAssembleiaPrazoTecnicoEnum
    {
        $periodo = $this->sindicoMandatoPeriodoAtual($baseDate);

        if ($periodo === null) {
            return null;
        }

        return $periodo['status'];
    }

    public function sindicoMandatoPeriodoAtual(?Carbon $baseDate = null): ?array
    {
        $mandatoFim = $this->mandato_fim?->copy()->startOfDay();
        $numDayControl = $this->num_day_control_sindico ?? $this->num_day_control;

        if (! $mandatoFim || ! $numDayControl) {
            return null;
        }

        $today = ($baseDate ?? now())->startOfDay();

        if ($today->gt($mandatoFim)) {
            return [
                'status' => IssuerAssembleiaPrazoTecnicoEnum::ATRASADO,
                'inicio' => null,
                'fim' => null,
                'faixa' => null,
            ];
        }

        $prazoTecnicoDias = $this->prazo_tecnico_sindico ?? 30;
        $inicioPrazo = $mandatoFim->copy()->subDays($prazoTecnicoDias);

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
        if ($faixaFim->gt($mandatoFim)) {
            $faixaFim = $mandatoFim->copy();
        }

        return [
            'status' => IssuerAssembleiaPrazoTecnicoEnum::fromIndex($faixa),
            'inicio' => $faixaInicio,
            'fim' => $faixaFim,
            'faixa' => $faixa,
        ];
    }

    public function getSindicoMandatoStatusAttribute(): ?IssuerAssembleiaPrazoTecnicoEnum
    {
        return $this->sindicoMandatoStatus();
    }

    public function getSindicoMandatoPeriodoAttribute(): ?array
    {
        return $this->sindicoMandatoPeriodoAtual();
    }

    public function conselhoMandatoStatus(?Carbon $baseDate = null): ?IssuerAssembleiaPrazoTecnicoEnum
    {
        $periodo = $this->conselhoMandatoPeriodoAtual($baseDate);

        if ($periodo === null) {
            return null;
        }

        return $periodo['status'];
    }

    public function conselhoMandatoPeriodoAtual(?Carbon $baseDate = null): ?array
    {
        $mandatoFim = $this->mandato_conselho_fim?->copy()->startOfDay();
        $numDayControl = $this->num_day_control_conselho ?? $this->num_day_control;

        if (! $mandatoFim || ! $numDayControl) {
            return null;
        }

        $today = ($baseDate ?? now())->startOfDay();

        if ($today->gt($mandatoFim)) {
            return [
                'status' => IssuerAssembleiaPrazoTecnicoEnum::ATRASADO,
                'inicio' => null,
                'fim' => null,
                'faixa' => null,
            ];
        }

        $prazoTecnicoDias = $this->prazo_tecnico_conselho ?? 30;
        $inicioPrazo = $mandatoFim->copy()->subDays($prazoTecnicoDias);

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
        if ($faixaFim->gt($mandatoFim)) {
            $faixaFim = $mandatoFim->copy();
        }

        return [
            'status' => IssuerAssembleiaPrazoTecnicoEnum::fromIndex($faixa),
            'inicio' => $faixaInicio,
            'fim' => $faixaFim,
            'faixa' => $faixa,
        ];
    }

    public function getConselhoMandatoStatusAttribute(): ?IssuerAssembleiaPrazoTecnicoEnum
    {
        return $this->conselhoMandatoStatus();
    }

    public function getConselhoMandatoPeriodoAttribute(): ?array
    {
        return $this->conselhoMandatoPeriodoAtual();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IssuerAssembleiaEventLog::class);
    }

    public function scopeAtrasadas($query)
    {
        return $query->whereNotNull('data_limite_edital')
            ->where('data_limite_edital', '<', today());
    }

    public function scopePorTipo($query, IssuerAgeTypeEnum $type)
    {
        return $query->where('type', $type);
    }

    public function scopePorPrazoTecnicoStatus($query, IssuerAssembleiaPrazoTecnicoEnum $status)
    {
        return match ($status) {
            IssuerAssembleiaPrazoTecnicoEnum::ANTES_DO_PRAZO => $query->where(function ($q) {
                $q->whereNotNull('data_limite_edital')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::PRIMEIRO => $query->where(function ($q) {
                $q->whereNotNull('data_limite_edital')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) <= CURDATE()')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) + INTERVAL num_day_control DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::SEGUNDO => $query->where(function ($q) {
                $q->whereNotNull('data_limite_edital')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) + INTERVAL num_day_control DAY <= CURDATE()')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) + INTERVAL (num_day_control * 2) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::TERCEIRO => $query->where(function ($q) {
                $q->whereNotNull('data_limite_edital')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) + INTERVAL (num_day_control * 2) DAY <= CURDATE()')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) + INTERVAL (num_day_control * 3) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::QUARTO => $query->where(function ($q) {
                $q->whereNotNull('data_limite_edital')
                    ->whereRaw('DATE_SUB(data_limite_edital, INTERVAL COALESCE(prazo_tecnico_edital, prazo_tecnico) DAY) + INTERVAL (num_day_control * 3) DAY <= CURDATE()')
                    ->where('data_limite_edital', '>=', today());
            }),
            IssuerAssembleiaPrazoTecnicoEnum::ATRASADO => $query->where(function ($q) {
                $q->whereNotNull('data_limite_edital')
                    ->where('data_limite_edital', '<', today());
            }),
        };
    }

    public function scopePorPrazoTecnicoSindicoStatus($query, IssuerAssembleiaPrazoTecnicoEnum $status)
    {
        return match ($status) {
            IssuerAssembleiaPrazoTecnicoEnum::ANTES_DO_PRAZO => $query->where(function ($q) {
                $q->whereNotNull('mandato_fim')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::PRIMEIRO => $query->where(function ($q) {
                $q->whereNotNull('mandato_fim')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) <= CURDATE()')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) + INTERVAL COALESCE(num_day_control_sindico, num_day_control) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::SEGUNDO => $query->where(function ($q) {
                $q->whereNotNull('mandato_fim')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) + INTERVAL COALESCE(num_day_control_sindico, num_day_control) DAY <= CURDATE()')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) + INTERVAL (COALESCE(num_day_control_sindico, num_day_control) * 2) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::TERCEIRO => $query->where(function ($q) {
                $q->whereNotNull('mandato_fim')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) + INTERVAL (COALESCE(num_day_control_sindico, num_day_control) * 2) DAY <= CURDATE()')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) + INTERVAL (COALESCE(num_day_control_sindico, num_day_control) * 3) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::QUARTO => $query->where(function ($q) {
                $q->whereNotNull('mandato_fim')
                    ->whereRaw('DATE_SUB(mandato_fim, INTERVAL COALESCE(prazo_tecnico_sindico, 30) DAY) + INTERVAL (COALESCE(num_day_control_sindico, num_day_control) * 3) DAY <= CURDATE()')
                    ->where('mandato_fim', '>=', today());
            }),
            IssuerAssembleiaPrazoTecnicoEnum::ATRASADO => $query->where(function ($q) {
                $q->whereNotNull('mandato_fim')
                    ->where('mandato_fim', '<', today());
            }),
        };
    }

    public function scopePorPrazoTecnicoConselhoStatus($query, IssuerAssembleiaPrazoTecnicoEnum $status)
    {
        return match ($status) {
            IssuerAssembleiaPrazoTecnicoEnum::ANTES_DO_PRAZO => $query->where(function ($q) {
                $q->whereNotNull('mandato_conselho_fim')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::PRIMEIRO => $query->where(function ($q) {
                $q->whereNotNull('mandato_conselho_fim')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) <= CURDATE()')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) + INTERVAL COALESCE(num_day_control_conselho, num_day_control) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::SEGUNDO => $query->where(function ($q) {
                $q->whereNotNull('mandato_conselho_fim')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) + INTERVAL COALESCE(num_day_control_conselho, num_day_control) DAY <= CURDATE()')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) + INTERVAL (COALESCE(num_day_control_conselho, num_day_control) * 2) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::TERCEIRO => $query->where(function ($q) {
                $q->whereNotNull('mandato_conselho_fim')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) + INTERVAL (COALESCE(num_day_control_conselho, num_day_control) * 2) DAY <= CURDATE()')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) + INTERVAL (COALESCE(num_day_control_conselho, num_day_control) * 3) DAY > CURDATE()');
            }),
            IssuerAssembleiaPrazoTecnicoEnum::QUARTO => $query->where(function ($q) {
                $q->whereNotNull('mandato_conselho_fim')
                    ->whereRaw('DATE_SUB(mandato_conselho_fim, INTERVAL COALESCE(prazo_tecnico_conselho, 30) DAY) + INTERVAL (COALESCE(num_day_control_conselho, num_day_control) * 3) DAY <= CURDATE()')
                    ->where('mandato_conselho_fim', '>=', today());
            }),
            IssuerAssembleiaPrazoTecnicoEnum::ATRASADO => $query->where(function ($q) {
                $q->whereNotNull('mandato_conselho_fim')
                    ->where('mandato_conselho_fim', '<', today());
            }),
        };
    }
}
