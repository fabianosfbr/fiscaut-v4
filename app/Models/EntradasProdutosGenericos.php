<?php

namespace App\Models;


use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EntradasProdutosGenericos extends Model
{

    protected $guarded = ['id'];

    protected $casts = [
        'cfop_entrada' => 'array',
        'valores' => 'array',
    ];

    protected function ncm(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => sprintf('%08d', $value),
        );
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($item) {
            Cache::forget('grupo_entradas_produtos_genericos_' . $item->tenant_id);
        });
    }
}
