<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogSefazNfseEvent extends Model
{
    protected $table = 'log_sefaz_nfse_events';

    protected $guarded = ['id'];

    protected $casts = [
        'dh_evento' => 'datetime',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class, 'issuer_id');
    }
}
