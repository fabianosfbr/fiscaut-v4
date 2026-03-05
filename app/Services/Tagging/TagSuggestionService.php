<?php

namespace App\Services\Tagging;

use App\Models\ConhecimentoTransporteEletronico;
use App\Models\NotaFiscalEletronica;
use App\Models\NotaFiscalServico;
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

    public function mostAppliedTagIdForNfeEmitentes(array $emitenteCnpjs, int $issuerId): array
    {
        $emitenteCnpjs = array_values(array_unique(array_filter(array_map(function ($cnpj) {
            $cnpj = trim((string) $cnpj);

            return $cnpj !== '' ? $cnpj : null;
        }, $emitenteCnpjs))));

        if ($emitenteCnpjs === []) {
            return [];
        }

        $rows = DB::table('nfes')
            ->join('tagging_tagged', function ($join) {
                $join->on('nfes.id', '=', 'tagging_tagged.taggable_id')
                    ->where('tagging_tagged.taggable_type', NotaFiscalEletronica::class);
            })
            ->join('tagging_tags', 'tagging_tags.id', '=', 'tagging_tagged.tag_id')
            ->join('categories_tag', 'categories_tag.id', '=', 'tagging_tags.category_id')
            ->whereIn('nfes.emitente_cnpj', $emitenteCnpjs)
            ->where('categories_tag.issuer_id', $issuerId)
            ->where('tagging_tags.is_enable', true)
            ->select(
                'nfes.emitente_cnpj as cnpj',
                'tagging_tagged.tag_id',
                DB::raw('COUNT(*) AS qtde'),
                DB::raw('MAX(tagging_tagged.id) AS max_tagged_id'),
            )
            ->groupBy('cnpj', 'tagging_tagged.tag_id')
            ->orderBy('cnpj')
            ->orderByDesc('qtde')
            ->orderByDesc('max_tagged_id')
            ->orderBy('tagging_tagged.tag_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $cnpj = (string) ($row->cnpj ?? '');
            if ($cnpj === '' || array_key_exists($cnpj, $result)) {
                continue;
            }

            $result[$cnpj] = (int) $row->tag_id;
        }

        return $result;
    }

    public function mostAppliedTagIdForNfsePrestadores(array $prestadorCnpjs, int $issuerId): array
    {
        $prestadorCnpjs = array_values(array_unique(array_filter(array_map(function ($cnpj) {
            $cnpj = trim((string) $cnpj);

            return $cnpj !== '' ? $cnpj : null;
        }, $prestadorCnpjs))));

        if ($prestadorCnpjs === []) {
            return [];
        }

        $rows = DB::table('nfses')
            ->join('tagging_tagged', function ($join) {
                $join->on('nfses.id', '=', 'tagging_tagged.taggable_id')
                    ->where('tagging_tagged.taggable_type', NotaFiscalServico::class);
            })
            ->join('tagging_tags', 'tagging_tags.id', '=', 'tagging_tagged.tag_id')
            ->join('categories_tag', 'categories_tag.id', '=', 'tagging_tags.category_id')
            ->whereIn('nfses.prestador_cnpj', $prestadorCnpjs)
            ->where('categories_tag.issuer_id', $issuerId)
            ->where('tagging_tags.is_enable', true)
            ->select(
                'nfses.prestador_cnpj as cnpj',
                'tagging_tagged.tag_id',
                DB::raw('COUNT(*) AS qtde'),
                DB::raw('MAX(tagging_tagged.id) AS max_tagged_id'),
            )
            ->groupBy('cnpj', 'tagging_tagged.tag_id')
            ->orderBy('cnpj')
            ->orderByDesc('qtde')
            ->orderByDesc('max_tagged_id')
            ->orderBy('tagging_tagged.tag_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $cnpj = (string) ($row->cnpj ?? '');
            if ($cnpj === '' || array_key_exists($cnpj, $result)) {
                continue;
            }

            $result[$cnpj] = (int) $row->tag_id;
        }

        return $result;
    }

    public function mostAppliedTagIdForCteEmitentes(array $emitenteCnpjs, int $issuerId): array
    {
        $emitenteCnpjs = array_values(array_unique(array_filter(array_map(function ($cnpj) {
            $cnpj = trim((string) $cnpj);

            return $cnpj !== '' ? $cnpj : null;
        }, $emitenteCnpjs))));

        if ($emitenteCnpjs === []) {
            return [];
        }

        $rows = DB::table('ctes')
            ->join('tagging_tagged', function ($join) {
                $join->on('ctes.id', '=', 'tagging_tagged.taggable_id')
                    ->where('tagging_tagged.taggable_type', ConhecimentoTransporteEletronico::class);
            })
            ->join('tagging_tags', 'tagging_tags.id', '=', 'tagging_tagged.tag_id')
            ->join('categories_tag', 'categories_tag.id', '=', 'tagging_tags.category_id')
            ->whereIn('ctes.emitente_cnpj', $emitenteCnpjs)
            ->where('categories_tag.issuer_id', $issuerId)
            ->where('tagging_tags.is_enable', true)
            ->select(
                'ctes.emitente_cnpj as cnpj',
                'tagging_tagged.tag_id',
                DB::raw('COUNT(*) AS qtde'),
                DB::raw('MAX(tagging_tagged.id) AS max_tagged_id'),
            )
            ->groupBy('cnpj', 'tagging_tagged.tag_id')
            ->orderBy('cnpj')
            ->orderByDesc('qtde')
            ->orderByDesc('max_tagged_id')
            ->orderBy('tagging_tagged.tag_id')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $cnpj = (string) ($row->cnpj ?? '');
            if ($cnpj === '' || array_key_exists($cnpj, $result)) {
                continue;
            }

            $result[$cnpj] = (int) $row->tag_id;
        }

        return $result;
    }
}
