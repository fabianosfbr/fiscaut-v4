<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogSefazNfeEvent extends Model
{
    
    public $table = 'log_sefaz_nfe_events';

    protected $guarded = ['id'];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class, 'issuer_id');
    }
}
