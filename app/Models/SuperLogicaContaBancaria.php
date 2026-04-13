<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperLogicaContaBancaria extends Model
{
    protected $table = 'super_logica_conta_bancarias';

    protected $guarded = ['id'];

    protected $casts = [
        'metadados' => 'array',
    ];
}
