<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeApurada extends Model
{
    protected $table = 'nfe_apuradas';

    protected $guarded = ['id'];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function nfe()
    {
        return $this->belongsTo(NotaFiscalEletronica::class, 'nfe_id');
    }

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }
}
