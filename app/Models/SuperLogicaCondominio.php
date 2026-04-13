<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperLogicaCondominio extends Model
{
    protected $table = 'super_logica_condominios';

    protected $guarded = ['id'];

    protected $casts = [
        'metadados' => 'array',
    ];

    public function unidades()
    {
        return $this->hasMany(SuperLogicaUnidade::class, 'id_condominio', 'id_condominio_cond');
    }


}
