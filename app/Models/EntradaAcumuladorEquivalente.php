<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EntradaAcumuladorEquivalente extends Model
{
    protected $table = 'entradas_acumuladores_equivalentes';

    const CACHE_PREFIX = 'entradas_acumuladores_equivalentes';

    protected $guarded = ['id'];

    protected $casts = [
        'valores' => 'array',
        'cfops' => 'array',
    ];

    public static function getAllCached(?int $issuerId, ?int $tenantId, $tipo)
    {
        $cacheKey = self::getCacheKey($issuerId, $tenantId, $tipo);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($issuerId, $tenantId, $tipo) {
            return static::where('issuer_id', $issuerId)
                ->where('tenant_id', $tenantId)
                ->where('tipo', $tipo)
                ->get();
        });
    }

    public static function invalidateCache(int $issuerId, int $tenantId, $tipo): void
    {
        $cacheKey = self::getCacheKey($issuerId, $tenantId, $tipo);
        Cache::forget($cacheKey);
    }

    public static function getCacheKey(int $issuerId, int $tenantId, $tipo)
    {
        return self::CACHE_PREFIX."_{$issuerId}_{$tenantId}_{$tipo}";
    }
}
