<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SuperLogicaCobrancaNotification extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $table = 'super_logica_cobranca_notifications';

    protected $with = ['issuer', 'unidade'];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
    ];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class, 'issuer_id');
    }

    public function unidade()
    {
        return $this->belongsTo(SuperLogicaUnidade::class, 'id_unidade_uni', 'id_unidade_uni');
    }
}
