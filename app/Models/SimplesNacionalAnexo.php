<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimplesNacionalAnexo extends Model
{
    protected $table = 'simples_nacional_anexos';

    protected $fillable = [
        'anexo',
        'descricao',
        'ativo'
    ];
    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function aliquotas(): HasMany
    {
        return $this->hasMany(SimplesNacionalAliquota::class, 'anexo', 'anexo');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }
}
