<?php

namespace App\Models;

use App\Enums\StatusCteEnum;
use App\Models\Traits\HasTags;
use App\Services\Xml\XmlReaderService;
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

    public function getCfopAttribute(): int
    {
        $xml = $this->extrairXmlComoString();
        if ($xml === null) {
            return '';
        }
        $data = (new XmlReaderService)->read($xml);

        return $data['cteProc']['CTe']['infCte']['ide']['CFOP'] ?? null;
    }

    private function extrairXmlComoString(): ?string
    {

        $raw = $this->xml ?? null;
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $uncompressed = @gzuncompress($raw);
        if (is_string($uncompressed) && $uncompressed !== '') {
            return $uncompressed;
        }

        return $raw;
    }
}
