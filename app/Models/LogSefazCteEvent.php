<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogSefazCteEvent extends Model
{
    public $table = 'log_sefaz_cte_events';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class, 'issuer_id');
    }
}
