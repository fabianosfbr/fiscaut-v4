<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ParametroSuperLogica extends Model
{
    protected $with = ['contaCredito', 'contaDebito'];

    protected $table = 'contabil_parametros_gerais_super_logica';

    protected $guarded = ['id'];

    protected $casts = [
        'params' => 'array',
        'check_value' => 'boolean',
    ];

    public function contaCredito()
    {
        return $this->belongsTo(PlanoDeConta::class, 'conta_credito', 'id');
    }

    public function contaDebito()
    {
        return $this->belongsTo(PlanoDeConta::class, 'conta_debito', 'id');
    }

    public function scopeSearchByParametro(Builder $query, string $search): Builder
    {
        return $query->whereRaw(
            "JSON_SEARCH(params COLLATE utf8mb4_general_ci, 'one', ? COLLATE utf8mb4_general_ci, null, '$') IS NOT NULL",
            ["%{$search}%"]
        );
    }
}
