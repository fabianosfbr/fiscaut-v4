<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issuer extends Model
{
    protected $guarded = ['id'];


    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
