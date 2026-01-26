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
            Cache::forget("category_tag_.{$category->issuer_id}._all");
        });

        static::deleted(function ($category) {
            Cache::forget("category_tag_.{$category->issuer_id}._all");
        });

        static::updated(function ($category) {
            Cache::forget("category_tag_.{$category->issuer_id}._all");
        });
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
