<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class SimplesNacionalAliquota extends Model
{
    protected $table = 'simples_nacional_aliquotas';

    protected $guarded = ['id'];

    protected $casts = [
        'faixa_inicial' => 'decimal:2',
        'faixa_final' => 'decimal:2',
        'aliquota' => 'decimal:4',
        'valor_deduzir' => 'decimal:2',
        'irpj_percentual' => 'decimal:2',
        'csll_percentual' => 'decimal:2',
        'cofins_percentual' => 'decimal:2',
        'pis_percentual' => 'decimal:2',
        'cpp_percentual' => 'decimal:2',
        'ipi_percentual' => 'decimal:2',
        'icms_percentual' => 'decimal:2',
        'iss_percentual' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $model) {
            if ($model->ipi_percentual === null) {
                $model->ipi_percentual = 0;
            }

            $percentualFields = [
                'aliquota',
                'irpj_percentual',
                'csll_percentual',
                'cofins_percentual',
                'pis_percentual',
                'cpp_percentual',
                'ipi_percentual',
                'icms_percentual',
                'iss_percentual',
            ];

            foreach ($percentualFields as $field) {
                $value = $model->{$field};
                if ($value === null) {
                    continue;
                }

                if ((float) $value < 0 || (float) $value > 100) {
                    throw ValidationException::withMessages([
                        $field => 'O percentual deve estar entre 0 e 100.',
                    ]);
                }
            }

            $nonNegativeMoneyFields = [
                'faixa_inicial',
                'faixa_final',
                'valor_deduzir',
            ];

            foreach ($nonNegativeMoneyFields as $field) {
                $value = $model->{$field};
                if ($value === null) {
                    continue;
                }

                if ((float) $value < 0) {
                    throw ValidationException::withMessages([
                        $field => 'O valor não pode ser negativo.',
                    ]);
                }
            }

            if ($model->anexo === null || $model->faixa_inicial === null || $model->faixa_final === null) {
                return;
            }

            if ((float) $model->faixa_inicial > (float) $model->faixa_final) {
                throw ValidationException::withMessages([
                    'faixa_final' => 'A faixa final deve ser maior ou igual à faixa inicial.',
                ]);
            }

            $overlapExists = self::query()
                ->where('anexo', $model->anexo)
                ->when($model->exists, fn ($query) => $query->where('id', '!=', $model->id))
                ->where('faixa_inicial', '<=', $model->faixa_final)
                ->where('faixa_final', '>=', $model->faixa_inicial)
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'faixa_inicial' => 'Já existe uma faixa cadastrada que se sobrepõe a este intervalo para o mesmo anexo.',
                    'faixa_final' => 'Já existe uma faixa cadastrada que se sobrepõe a este intervalo para o mesmo anexo.',
                ]);
            }
        });
    }

    public function anexoModel(): BelongsTo
    {
        return $this->belongsTo(SimplesNacionalAnexo::class, 'anexo', 'anexo');
    }

    public function scopeForAnexo($query, string $anexo)
    {
        return $query->where('anexo', $anexo);
    }

    public function scopeForFaturamento($query, float $faturamento)
    {
        return $query->where('faixa_inicial', '<=', $faturamento)
            ->where('faixa_final', '>=', $faturamento);
    }

    public function getDetalhamentoImpostosAttribute(): array
    {
        return [
            'irpj' => $this->irpj_percentual,
            'csll' => $this->csll_percentual,
            'cofins' => $this->cofins_percentual,
            'pis' => $this->pis_percentual,
            'cpp' => $this->cpp_percentual,
            'ipi' => $this->ipi_percentual,
            'icms' => $this->icms_percentual,
            'iss' => $this->iss_percentual,
        ];
    }
}
