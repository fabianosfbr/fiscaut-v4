<?php

namespace App\Models;

use App\Enums\StatusNfeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotaFiscalConsumidor extends Model
{
    use HasUuids;

    protected $table = 'nfces';

    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'string',
        'data_emissao' => 'datetime',
        'status_nota' => StatusNfeEnum::class,
    ];
}
