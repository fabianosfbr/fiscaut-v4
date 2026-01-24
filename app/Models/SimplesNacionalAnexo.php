<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimplesNacionalAnexo extends Model
{
    use HasFactory;

    protected $table = 'simples_nacional_anexos';

    protected $fillable = [
        'anexo',
        'descricao',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean'
    ];

    public function aliquotas(): HasMany
    {
        return $this->hasMany(SimplesNacionalAliquota::class, 'anexo', 'anexo');
    }

    public function cnaes(): HasMany
    {
        return $this->hasMany(SimplesNacionalCnae::class, 'anexo', 'anexo');
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(SimplesNacionalCalculation::class, 'anexo', 'anexo');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }
}
