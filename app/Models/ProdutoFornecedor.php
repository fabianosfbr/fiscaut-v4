<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProdutoFornecedor extends Model
{
    use HasUuids;

    protected $table = 'produto_fornecedores';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * Find or create a ProdutoFornecedor record
     */
    public static function findOrCreate(array $attributes): static
    {
        $record = static::where($attributes)->first();

        if (! $record) {
            $record = static::create($attributes);
        }

        return $record;
    }
}
