<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperLogicaUnidade extends Model
{
     protected $table = 'super_logica_unidades';

    protected $guarded = ['id'];

    protected $casts = [
        'metadados' => 'array',
    ];
}
