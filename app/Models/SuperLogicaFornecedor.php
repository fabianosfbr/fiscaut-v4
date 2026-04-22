<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperLogicaFornecedor extends Model
{
    protected $table = 'super_logica_fornecedores';

    protected $guarded = ['id'];

    protected $casts = [
        'metadados' => 'array',
    ];
}
