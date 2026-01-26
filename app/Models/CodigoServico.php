<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoServico extends Model
{
    protected $table = 'codigos_servico';

    protected $fillable = [
        'codigo',
        'descricao',
        'cnae_id',
    ];

    /**
     * Relacionamento com CNAE
     * Um código de serviço pertence a um CNAE
     */
    public function cnae()
    {
        return $this->belongsTo(Cnae::class, 'cnae_id');
    }
}
