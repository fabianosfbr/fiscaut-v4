<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogSefazNfseEvent extends Model
{
    protected $table = 'log_sefaz_nfse_events';

    protected $guarded = ['id'];

    protected $casts = [
        'dh_evento' => 'datetime',
    ];
}
