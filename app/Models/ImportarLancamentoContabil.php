<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ImportarLancamentoContabil extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $table = 'contabil_importar_lancamento_contabeis';

    protected $casts = [
        'metadata' => 'array',
        'data' => 'date',
    ];
}
