<?php

namespace App\Models;

use App\Enums\IssuerAgeTypeEnum;
use App\Enums\IssuerAssembleiaStatusAtaEnum;
use Illuminate\Database\Eloquent\Model;

class IssuerAssembleia extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'vigencia_date' => 'date',
        'data_limite_edital' => 'date',
        'data_limite_ago' => 'date',
        'mandato_fim' => 'date',
        'mandato_conselho_fim' => 'date',
        'mandato_banco_fim' => 'date',
        'tem_isencao_remuneracao' => 'array',
        'tem_isencao' => 'boolean',
        'tem_remuneracao' => 'boolean',
        'quem_recebe_isencao' => 'array',
        'quem_recebe_remuneracao' => 'array',
        'valor_isencao' => 'decimal:2',
        'valor_remuneracao' => 'decimal:2',
        'type' => IssuerAgeTypeEnum::class,
        'status_ata' => IssuerAssembleiaStatusAtaEnum::class,
    ];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }
}
