<?php

namespace App\Models;

use App\Enums\IssuerControlFrequencyEnum;
use App\Enums\IssuerControlPriorityEnum;
use App\Enums\IssuerControlTypeEnum;
use App\Models\Issuer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuerControlType extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'categoria' => IssuerControlTypeEnum::class,
        'periodicidade_padrao' => IssuerControlFrequencyEnum::class,
        'prioridade' => IssuerControlPriorityEnum::class,
        'alerta_dias_antecedencia' => 'integer',
        'ativo' => 'boolean',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorCategoria($query, IssuerControlTypeEnum $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorPrioridade($query, IssuerControlPriorityEnum $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }
}
