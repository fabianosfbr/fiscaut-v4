<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class IssuerUserPermission extends Pivot
{
    protected $table = 'users_issuers_permissions';

    protected $fillable = [
        'user_id',
        'issuer_id',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];
}
