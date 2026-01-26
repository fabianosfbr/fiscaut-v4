<?php

namespace App\Models;

use App\Enums\StatusCteEnum;
use App\Models\Traits\HasTags;
use Illuminate\Database\Eloquent\Model;

class ConhecimentoTransporteEletronico extends Model
{
    use HasTags;

    
    protected $table = 'ctes';

    protected $guarded = ['id'];

    protected $casts = [
        'data_emissao' => 'date',
        'status_cte' => StatusCteEnum::class,
        'metadata' => 'array',
        // 'tpCTe' => TipoCteEnum::class,
    ];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }
}
