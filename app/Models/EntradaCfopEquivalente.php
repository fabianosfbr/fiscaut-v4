<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradaCfopEquivalente extends Model
{
    protected $table = 'entradas_cfops_equivalentes';

    protected $guarded = ['id'];

    protected $casts = [
        'valores' => 'array',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoEntradaCfopEquivalente::class, 'grupo_id');
    }
}
