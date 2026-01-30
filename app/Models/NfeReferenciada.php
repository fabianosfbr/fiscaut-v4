<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeReferenciada extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function nfe()
    {
        return $this->belongsTo(NotaFiscalEletronica::class);
    }
}
