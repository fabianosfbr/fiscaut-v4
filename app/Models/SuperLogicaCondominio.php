<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperLogicaCondominio extends Model
{
    protected $table = 'super_logica_condominios';

    protected $guarded = ['id'];


    public function issuer()
    {
        return $this->belongsTo(Issuer::class, 'issuer_id', 'id');
    }
}
