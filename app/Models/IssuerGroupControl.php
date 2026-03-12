<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssuerGroupControl extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(IssuerControlField::class, 'issuer_group_control_id');
    }
}
