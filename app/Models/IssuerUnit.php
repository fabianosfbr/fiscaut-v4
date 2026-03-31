<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuerUnit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'area' => 'decimal:2',
        'fraction' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }
}
