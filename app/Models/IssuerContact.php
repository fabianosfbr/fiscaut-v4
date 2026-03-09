<?php

namespace App\Models;

use App\Enums\IssuerContactRoleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuerContact extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'funcao' => IssuerContactRoleEnum::class,
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }
}
