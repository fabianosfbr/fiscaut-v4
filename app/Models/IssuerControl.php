<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuerControl extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'value' => 'array',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(IssuerControlField::class, 'issuer_control_field_id');
    }
}
