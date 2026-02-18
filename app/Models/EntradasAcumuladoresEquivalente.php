<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class EntradasAcumuladoresEquivalente extends Model
{

    protected $guarded = ['id'];

    protected $casts = [
        'valores' => 'array',
        'cfops' => 'array',
    ];


}
