<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $guarded = ['id'];

    public function issuers()
    {
        return $this->hasMany(Issuer::class);
    }

    public function areaResponsibles()
    {
        return $this->hasMany(IssuerAreaResponsible::class);
    }
}
