<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CategoryTag extends Model
{
    protected $table = 'categories_tag';

    protected $guarded = ['id'];

    protected $casts = [
        'is_enable' => 'boolean',
        'is_difal' => 'boolean',
        'is_devolucao' => 'boolean',
        'order' => 'integer',
        'grupo' => 'integer',
        'conta_contabil' => 'integer',
        'tipo_item' => 'integer',
    ];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'category_id');
    }

    public function activeTags()
    {
        return $this->hasMany(Tag::class, 'category_id')
            ->where('is_enable', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($category) {
            self::flushAllCaches($category);
        });

        static::deleted(function ($category) {
            self::flushAllCaches($category);
        });

        static::updated(function ($category) {
            self::flushAllCaches($category);
        });
    }

    private static function flushAllCaches($category): void
    {
        $issuerId = $category->issuer_id;

        if (! $issuerId) {
            return;
        }

        $cacheKeys = [
            "category_tag_{$issuerId}_all",
            "tags_used_in_upload_file_{$issuerId}",
            "tags_used_in_nfe_grouped_{$issuerId}",
            "tags_used_in_cte_grouped_{$issuerId}",
            "tags_used_in_nfse_grouped_{$issuerId}",
            "tags_used_in_nfe_{$issuerId}",
            "tags_used_in_cte_{$issuerId}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    public static function getAllEnabled(string $issuerId)
    {
        return Cache::remember("category_tag_{$issuerId}_all", now()->addDay(), function () use ($issuerId) {
            return static::with(['tags' => function ($query) {
                $query->where('is_enable', true);
            }])
                ->where('issuer_id', $issuerId)
                ->where('is_enable', true)
                ->orderBy('order', 'asc')
                ->get();
        });
    }
}
