<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplesNacionalCnae extends Model
{
    protected $table = 'simples_nacional_cnaes';

    protected $fillable = [
        'codigo_cnae',
        'descricao',
        'anexo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
