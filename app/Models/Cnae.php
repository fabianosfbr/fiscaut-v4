<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cnae extends Model
{
    protected $fillable = [
        'codigo',
        'descricao',
        'anexo',
        'fator_r',
        'aliquota',
    ];

    /**
     * Relacionamento com Códigos de Serviço
     * Um CNAE pode ter vários códigos de serviço
     */
    public function codigosServico()
    {
        return $this->hasMany(CodigoServico::class, 'cnae_id');
    }
}
