<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CteReferencia extends Model
{
    protected $table = 'cte_referencias';

    public $timestamps = false;

    protected $fillable = [
        'cte_id',
        'nfe_referenciada_id',
    ];

    public function cte()
    {
        return $this->belongsTo(ConhecimentoTransporteEletronico::class, 'cte_id');
    }

    public function nfeReferenciada()
    {
        return $this->belongsTo(NotaFiscalEletronica::class, 'nfe_referenciada_id');
    }
}
