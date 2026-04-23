<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SuperLogicaCobrancaNotification extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $table = 'super_logica_cobranca_notifications';


    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
    ];

}