<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ParametroGeral extends Model
{
    protected $with = ['plano_de_conta'];
    protected $table = 'contabil_parametros_gerais';

    protected $guarded = ['id'];


    protected $casts = [
        'params' => 'array',
        'descricao_conta_contabil' => 'array',
        'codigo' => 'array',
        'descricao' => 'array',
        'complemento_historico' => 'array',
        'descricao_historico' => 'array',
        'is_inclusivo' => 'boolean',
    ];

    //Plano de contas
    public function plano_de_conta()
    {
        return $this->belongsTo(PlanoDeConta::class, 'conta_contabil', 'id');
    }

    public function scopeSearchByParametro(Builder $query, string $search): Builder
    {
        return $query->whereRaw(
            "JSON_SEARCH(params COLLATE utf8mb4_general_ci, 'one', ? COLLATE utf8mb4_general_ci, null, '$') IS NOT NULL",
            ["%{$search}%"]
        );
    }
}
