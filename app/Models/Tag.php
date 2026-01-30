<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Tag extends Model
{
    protected $table = 'tagging_tags';

    protected $guarded = ['id'];

    protected $with = ['category'];

    protected $appends = ['namecode'];

    public function category()
    {
        return $this->belongsTo(CategoryTag::class, 'category_id');
    }

    public function getNameCodeAttribute()
    {
        return "{$this->code} - {$this->name}";
    }

    public static function getTagsUsedInUploadFile(): array
    {
        if (! Auth::check() || ! Auth::user()->currentIssuer) {
            return [];
        }

        $issuerId = Auth::user()->currentIssuer->id;
        $cacheKey = 'tags_used_in_upload_file_'.$issuerId;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($issuerId) {
            $tagIds = self::rightJoin('tagging_tagged', 'tagging_tags.id', '=', 'tagging_tagged.tag_id')
                ->where('tagging_tagged.taggable_type', UploadFile::class)
                ->whereHas('category', function ($query) use ($issuerId) {
                    $query->where('issuer_id', $issuerId);
                })
                ->where('is_enable', true)
                ->select('tagging_tags.id')
                ->distinct()
                ->pluck('id');

            return self::whereIn('id', $tagIds)
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('id')
                ->map(fn ($tag) => $tag->code.' - '.$tag->name)
                ->toArray();
        });
    }

    public static function getTagsUsedInNfe(): array
    {
        if (! Auth::check() || ! Auth::user()->currentIssuer) {
            return [];
        }

        $issuerId = Auth::user()->currentIssuer->id;
        $cacheKey = 'tags_used_in_nfe_'.$issuerId;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($issuerId) {
            $tagIds = self::rightJoin('tagging_tagged', 'tagging_tags.id', '=', 'tagging_tagged.tag_id')
                ->where('tagging_tagged.taggable_type', NotaFiscalEletronica::class)
                ->whereHas('category', function ($query) use ($issuerId) {
                    $query->where('issuer_id', $issuerId);
                })
                ->where('is_enable', true)
                ->select('tagging_tags.id')
                ->distinct()
                ->pluck('id');

            return self::whereIn('id', $tagIds)
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('id')
                ->map(fn ($tag) => $tag->code.' - '.$tag->name)
                ->toArray();
        });
    }

    public static function getTagsUsedInCte(): array
    {
        if (! Auth::check() || ! Auth::user()->currentIssuer) {
            return [];
        }

        $issuerId = Auth::user()->currentIssuer->id;
        $cacheKey = 'tags_used_in_cte_'.$issuerId;

        return Cache::remember($cacheKey, now()->addDay(), function () use ($issuerId) {
            $tagIds = self::rightJoin('tagging_tagged', 'tagging_tags.id', '=', 'tagging_tagged.tag_id')
                ->where('tagging_tagged.taggable_type', ConhecimentoTransporteEletronico::class)
                ->whereHas('category', function ($query) use ($issuerId) {
                    $query->where('issuer_id', $issuerId);
                })
                ->where('is_enable', true)
                ->select('tagging_tags.id')
                ->distinct()
                ->pluck('id');

            return self::whereIn('id', $tagIds)
                ->orderBy('name', 'asc')
                ->get()
                ->keyBy('id')
                ->map(fn ($tag) => $tag->code.' - '.$tag->name)
                ->toArray();
        });
    }
}
