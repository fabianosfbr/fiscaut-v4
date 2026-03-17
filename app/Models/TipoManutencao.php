<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoManutencao extends Model
{
    protected $table = 'tipos_manutencao';

    protected $guarded = ['id'];

    public function tenants()
    {
        return $this->belongsTo(Tenant::class);
    }
}
