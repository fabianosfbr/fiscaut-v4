<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class GrupoEntradasProdutosGenerico extends Model
{
    protected $table = 'grupo_entradas_produtos_genericos';

    const CACHE_PREFIX = 'grupo_entradas_produtos_genericos';

    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'json',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(EntradasProdutosGenerico::class, 'grupo_id');
    }

    public static function getAllCached(?int $issuerId = null, ?int $tenantId = null)
    {
        $cacheKey = self::getCacheKey($issuerId, $tenantId);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($issuerId, $tenantId) {
            return static::with('produtos')
                ->where('issuer_id', $issuerId)
                ->where('tenant_id', $tenantId)
                ->get();
        });
    }

    public static function invalidateCache(int $issuerId, int $tenantId): void
    {
        $cacheKey = self::getCacheKey($issuerId, $tenantId);
        Cache::forget($cacheKey);
    }

    public static function getCacheKey(int $issuerId, int $tenantId)
    {
        return self::CACHE_PREFIX."_{$issuerId}_{$tenantId}";
    }
}
