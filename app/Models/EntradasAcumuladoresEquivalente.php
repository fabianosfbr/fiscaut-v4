<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntradasAcumuladoresEquivalente extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'valores' => 'array',
        'cfops' => 'array',
    ];
}
