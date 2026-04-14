<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperLogicaPlanoDeConta extends Model
{
    protected $table = 'super_logica_plano_de_contas';

    protected $guarded = ['id'];

    protected $casts = [
        'metadados' => 'array',
    ];
}
