<?php

namespace App\Models;

use App\Models\Traits\HasTags;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotaFiscalServico extends Model
{

    use HasTags;


    protected $table = 'nfses';

    protected $guarded = ['id'];

    protected $casts = [
        'data_emissao' => 'datetime',
        'import_data' => 'array',
    ];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function scopeSearch($query, $term)
    {
        $term = "%$term%";
        $query->where(function ($query) use ($term) {
            $query->where('tomador_servico', 'like', $term)
                ->orWhere('tomador_cnpj', 'like', $term)
                ->orWhere('prestador_servico', 'like', $term)
                ->orWhere('prestador_cnpj', 'like', $term)
                ->orWhere('prestador_im', 'like', $term)
                ->orWhere('data_emissao', 'like', $term)
                ->orWhere('valor_servico', 'like', $term)
                ->orWhere('codigo_verificacao', 'like', $term)
                ->orWhere('numero', 'like', $term);
        });
    }
}
