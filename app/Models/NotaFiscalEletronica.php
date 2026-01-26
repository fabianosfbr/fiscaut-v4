<?php

namespace App\Models;

use App\Enums\StatusNfeEnum;
use App\Models\Traits\HasTags;
use App\Enums\StatusManifestoNfe;
use App\Services\Xml\XmlReaderService;
use Illuminate\Database\Eloquent\Model;

class NotaFiscalEletronica extends Model
{
    use HasTags;

    
    protected $table = 'nfes';

    protected $guarded = ['id'];

    protected $casts = [
        'data_emissao' => 'datetime',
        'data_entrada' => 'datetime',
        'status_nota' => StatusNfeEnum::class,
        'status_manifestacao' => StatusManifestoNfe::class,
        'carta_correcao' => 'array',
        'difal' => 'array',
        'cobranca' => 'array',
        'parcela' => 'array',
        'cfops' => 'array',
    ];

    public function nfeReferenciada()
    {
        return $this->hasMany(NfeReferenciada::class, 'nfe_id', 'id');
    }

    public function getProdutos(): array
    {
        $xml = $this->extrairXmlComoString();
        if ($xml === null) {
            return [];
        }

        $data = (new XmlReaderService())->read($xml);
        $det = $data['nfeProc']['NFe']['infNFe']['det'] ?? null;

        $detList = $this->normalizeList($det);
        if ($detList === []) {
            return [];
        }

        return array_values(array_map(function ($detItem) {
            if (!is_array($detItem)) {
                return [];
            }

            $prod = $detItem['prod'] ?? [];
            $imposto = $detItem['imposto'] ?? [];

            return [
                'nItem' => $detItem['@attributes']['nItem'] ?? null,
                'cProd' => $prod['cProd'] ?? null,
                'xProd' => $prod['xProd'] ?? null,
                'NCM' => $prod['NCM'] ?? null,
                'CFOP' => $prod['CFOP'] ?? null,
                'uCom' => $prod['uCom'] ?? null,
                'qCom' => $prod['qCom'] ?? null,
                'vUnCom' => $prod['vUnCom'] ?? null,
                'vProd' => $prod['vProd'] ?? null,
                'vDesc' => $prod['vDesc'] ?? null,
                'vFrete' => $prod['vFrete'] ?? null,
                'vSeg' => $prod['vSeg'] ?? null,
                'cEAN' => $prod['cEAN'] ?? null,
                'cEANTrib' => $prod['cEANTrib'] ?? null,
                'impostos' => [
                    'vBC' => searchValueInArray($imposto, 'vBC'),
                    'pICMS' => searchValueInArray($imposto, 'pICMS'),
                    'vICMS' => searchValueInArray($imposto, 'vICMS'),
                    'vIPI' => searchValueInArray($imposto, 'vIPI'),
                    'vPIS' => searchValueInArray($imposto, 'vPIS'),
                    'vCOFINS' => searchValueInArray($imposto, 'vCOFINS'),
                ],
            ];
        }, $detList));
    }

    private function extrairXmlComoString(): ?string
    {
        $raw = $this->xml ?? null;
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $uncompressed = @gzuncompress($raw);
        if (is_string($uncompressed) && $uncompressed !== '') {
            return $uncompressed;
        }

        return $raw;
    }

    private function normalizeList($value): array
    {
        if ($value === null) {
            return [];
        }

        if (!is_array($value)) {
            return [$value];
        }

        if ($value === []) {
            return [];
        }

        $keys = array_keys($value);
        $isList = $keys === range(0, count($keys) - 1);

        if ($isList) {
            return $value;
        }

        return [$value];
    }
}
