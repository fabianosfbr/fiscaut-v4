<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradasProdutosGenerico extends Model
{
    protected $table = 'entradas_produtos_genericos';

    protected $guarded = ['id'];

    protected $casts = [
        'ncm' => 'integer',
    ];

    protected function ncm(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => sprintf('%08d', $value),
        );
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoEntradasProdutosGenerico::class, 'grupo_id');
    }
}
