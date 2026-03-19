<?php

namespace App\Models;

use App\Enums\ManutencaoCategoriaEnum;
use App\Enums\ManutencaoFrequenciaEnum;
use App\Enums\ManutencaoPrioridadeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoManutencao extends Model
{
    protected $table = 'tipos_manutencao';

    protected $guarded = ['id'];

    protected $casts = [
        'categoria' => ManutencaoCategoriaEnum::class,
        'periodicidade_padrao' => ManutencaoFrequenciaEnum::class,
        'prioridade' => ManutencaoPrioridadeEnum::class,
        'alerta_dias_antecedencia' => 'integer',
        'ativo' => 'boolean',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function manutencoes(): HasMany
    {
        return $this->hasMany(Manutencao::class);
    }

    public function recorrencias(): HasMany
    {
        return $this->hasMany(ManutencaoRecorrencia::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function scopePorCategoria($query, ManutencaoCategoriaEnum $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorPrioridade($query, ManutencaoPrioridadeEnum $prioridade)
    {
        return $query->where('prioridade', $prioridade);
    }
}
