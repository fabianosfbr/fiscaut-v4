<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class GrupoEntradaCfopEquivalente extends Model
{
    protected $table = 'grupo_entradas_cfops_equivalentes';

    const CACHE_PREFIX = 'grupo_entradas_cfops_equivalentes';

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

    public function cfopsEquivalentes(): HasMany
    {
        return $this->hasMany(EntradaCfopEquivalente::class, 'grupo_id');
    }

    public static function getAllCached(?int $issuer_id, ?int $tenantId, $tipo)
    {
        $cacheKey = self::getCacheKey($issuer_id, $tenantId, $tipo);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($issuer_id, $tenantId, $tipo) {
            return static::whereHas('cfopsEquivalentes', function ($query) use ($tipo) {
                $query->where('tipo', $tipo);
            })
                ->where('issuer_id', $issuer_id)
                ->where('tenant_id', $tenantId)
                ->with('cfopsEquivalentes')
                ->get();
        });
    }

    public static function invalidateCache(int $issuer_id, int $tenantId, $tipo): void
    {
        $cacheKey = self::getCacheKey($issuer_id, $tenantId, $tipo);
        Cache::forget($cacheKey);
    }

    public static function getCacheKey(int $issuer_id, int $tenantId, $tipo)
    {
        return self::CACHE_PREFIX."_{$issuer_id}_{$tenantId}_{$tipo}";
    }
}
