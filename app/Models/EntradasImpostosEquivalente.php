<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EntradasImpostosEquivalente extends Model
{
    protected $guarded = ['id'];

    const CACHE_PREFIX = 'entradas_impostos_equivalentes';

    protected $table = 'entradas_impostos_equivalentes';

    protected $casts = [
        'status_icms' => 'boolean',
        'status_ipi' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Invalida cache quando o modelo é criado, atualizado ou deletado
        static::saved(function (self $model) {
            $model->invalidateCache($model->issuer_id, $model->tenant_id);
        });

        static::deleted(function (self $model) {
            $model->invalidateCache($model->issuer_id, $model->tenant_id);
        });

        static::updated(function (self $model) {
            $model->invalidateCache($model->issuer_id, $model->tenant_id);
        });
    }

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
        return $this->belongsToMany(CategoryTag::class, 'entradas_impostos_equivalentes_tags');
    }

    public static function getAllCached(?int $companyId = null, ?int $tenantId = null)
    {
        $cacheKey = self::getCacheKey($companyId, $tenantId);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($companyId, $tenantId) {
            return static::with('tags')
                ->where('issuer_id', $companyId)
                ->where('tenant_id', $tenantId)
                ->get();
        });
    }

    public static function invalidateCache(int $companyId, int $tenantId): void
    {
        $cacheKey = self::getCacheKey($companyId, $tenantId);
        Cache::forget($cacheKey);
    }

    public static function getCacheKey(int $companyId, int $tenantId)
    {
        return self::CACHE_PREFIX."_{$companyId}_{$tenantId}";
    }
}
