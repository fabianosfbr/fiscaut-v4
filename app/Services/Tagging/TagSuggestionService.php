<?php

namespace App\Services\Tagging;

use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TagSuggestionService
{
    public function forNfeEmitente(string $emitenteCnpj, int $limit = 10): Collection
    {
        $emitenteCnpj = trim($emitenteCnpj);

        if ($emitenteCnpj === '') {
            return collect();
        }

        return DB::table('issuers')
            ->join('nfes', 'issuers.cnpj', '=', 'nfes.destinatario_cnpj')
            ->leftJoin('tagging_tagged', 'nfes.id', '=', 'tagging_tagged.taggable_id')
            ->leftJoin('tagging_tags', 'tagging_tags.id', '=', 'tagging_tagged.tag_id')
            ->select(
                'tagging_tagged.tag_id',
                'tagging_tagged.tag_name',
                'tagging_tags.code',
                'tagging_tags.issuer_id',
                DB::raw('COUNT(*) AS qtde')
            )
            ->where('nfes.emitente_cnpj', $emitenteCnpj)
            ->where('tagging_tagged.taggable_type', NotaFiscalEletronica::class)
            ->groupBy('tagging_tagged.tag_id', 'tagging_tagged.tag_name', 'tagging_tags.issuer_id')
            ->havingRaw('COUNT(*) >= 1')
            ->limit($limit)
            ->orderByDesc('qtde')
            ->get();
    }
}
