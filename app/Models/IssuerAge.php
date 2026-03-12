<?php

namespace App\Models;

use App\Enums\IssuerAgeTypeEnum;
use Illuminate\Database\Eloquent\Model;

class IssuerAge extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'vigencia_date' => 'date',
        'data_limite_edital' => 'date',
        'data_limite_ago' => 'date',
        'mandato_fim' => 'date',
        'mandato_conselho_fim' => 'date',
        'mandato_banco_fim' => 'date',
        'tem_isencao_remuneracao' => 'boolean',
        'quem_recebe_isencao' => 'array',
        'valor_isencao_remuneracao' => 'decimal:2',
        'type' => IssuerAgeTypeEnum::class,
    ];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }
}
