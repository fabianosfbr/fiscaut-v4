<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeneralSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Cache TTL em segundos (24 horas)
     */
    const CACHE_TTL = 86400;

    /**
     * Prefixo para chaves de cache
     */
    const CACHE_PREFIX = 'general_settings';

    /**
     * Boot method para eventos de modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Invalida cache quando o modelo é criado, atualizado ou deletado
        static::saved(function (self $model) {
            $model->invalidateCache();
        });

        static::deleted(function (self $model) {
            $model->invalidateCache();
        });
    }

    /**
     * Get the tenant that owns this setting.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the issuer that owns this setting.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    /**
     * Gera chave de cache para configurações específicas
     */
    public static function getCacheKey(string $name, ?int $issuerId = null, ?int $tenantId = null): string
    {
        $parts = [self::CACHE_PREFIX, $name];

        if ($tenantId) {
            $parts[] = "tenant_{$tenantId}";
        }

        if ($issuerId) {
            $parts[] = "issuer_{$issuerId}";
        }

        return implode(':', $parts);
    }

    /**
     * Gera tags de cache para invalidação granular
     */
    public static function getCacheTags(?int $issuerId = null, ?int $tenantId = null): array
    {
        $tags = [self::CACHE_PREFIX];

        if ($tenantId) {
            $tags[] = self::CACHE_PREFIX."_tenant_{$tenantId}";
        }

        if ($issuerId) {
            $tags[] = self::CACHE_PREFIX."_issuer_{$issuerId}";
        }

        return $tags;
    }

    /**
     * Get a specific setting value by key with cache.
     */
    public static function getValue(string $name, string $key, $default = null, ?int $issuerId = null, ?int $tenantId = null)
    {
        // Se não informado, pega tenant do usuário atual
        if (! $tenantId && Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }

        $cacheKey = self::getCacheKey($name, $issuerId, $tenantId).":{$key}";

        try {
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($name, $key, $default, $issuerId, $tenantId) {
                $settings = self::getAll($name, $issuerId, $tenantId, false); // false = não usar cache na consulta interna

                return $settings[$key] ?? $default;
            });
        } catch (\Exception $e) {
            // Fallback para busca direta no banco se cache falhar
            Log::warning('Cache failed for GeneralSetting getValue, using database fallback', [
                'name' => $name,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            $settings = self::querySettings($name, $issuerId, $tenantId);

            return $settings[$key] ?? $default;
        }
    }

    /**
     * Set a specific setting value with cache invalidation.
     */
    public static function setValue(string $name, array $data, ?int $issuerId = null, ?int $tenantId = null): self
    {
        // Se não informado, pega tenant do usuário atual
        if (! $tenantId && Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }

        $query = static::where('name', $name);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($issuerId) {
            $query->where('issuer_id', $issuerId);
        }

        $setting = $query->first();

        if ($setting) {
            $setting->update([
                'payload' => array_merge($setting->payload ?? [], $data),
            ]);
        } else {
            $setting = static::create([
                'tenant_id' => $tenantId,
                'issuer_id' => $issuerId,
                'name' => $name,
                'payload' => $data,
            ]);
        }

        return $setting;
    }

    /**
     * Get all settings for a specific name with cache.
     */
    public static function getAll(string $name, ?int $issuerId = null, ?int $tenantId = null, bool $useCache = true): array
    {
        // Se não informado, pega tenant do usuário atual
        if (! $tenantId && Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }

        if (! $useCache) {
            return self::querySettings($name, $issuerId, $tenantId);
        }

        $cacheKey = self::getCacheKey($name, $issuerId, $tenantId);
        

        try {
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($name, $issuerId, $tenantId) {
                return self::querySettings($name, $issuerId, $tenantId);
            });
        } catch (\Exception $e) {
            // Fallback para busca direta no banco se cache falhar
            Log::warning('Cache failed for GeneralSetting getAll, using database fallback', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return self::querySettings($name, $issuerId, $tenantId);
        }
    }

    /**
     * Executa a consulta real ao banco de dados
     */
    private static function querySettings(string $name, ?int $issuerId = null, ?int $tenantId = null): array
    {
        $query = static::where('name', $name);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($issuerId) {
            $query->where('issuer_id', $issuerId);
        }

        $setting = $query->first();

        return $setting?->payload ?? [];
    }

    /**
     * Invalida cache para esta instância específica
     */
    public function invalidateCache(): void
    {
        $currentName = $this->name;
        $currentIssuerId = $this->issuer_id;
        $currentTenantId = $this->tenant_id;

        $originalName = $this->getOriginal('name') ?? $currentName;
        $originalIssuerId = $this->getOriginal('issuer_id') ?? $currentIssuerId;
        $originalTenantId = $this->getOriginal('tenant_id') ?? $currentTenantId;

        $currentKeys = self::payloadKeys($this->payload);
        $originalKeys = self::payloadKeys($this->getOriginal('payload'));

        self::invalidateCacheByParams($currentName, $currentIssuerId, $currentTenantId, $currentKeys);

        if (
            $originalName !== $currentName ||
            $originalIssuerId !== $currentIssuerId ||
            $originalTenantId !== $currentTenantId
        ) {
            self::invalidateCacheByParams($originalName, $originalIssuerId, $originalTenantId, $originalKeys);
        }
    }

    /**
     * Invalida cache por parâmetros específicos
     */
    public static function invalidateCacheByParams(string $name, ?int $issuerId = null, ?int $tenantId = null, ?array $keys = null): void
    {
        try {
            $cacheKey = self::getCacheKey($name, $issuerId, $tenantId);

            $keysToForget = $keys ?? array_keys(self::querySettings($name, $issuerId, $tenantId));

            self::forgetCacheKeys($cacheKey, $keysToForget);
            Cache::put(self::CACHE_PREFIX.':last_cleared', now()->toDateTimeString(), self::CACHE_TTL);

            // Log da invalidação para debugging
            Log::info('GeneralSetting cache invalidated', [
                'name' => $name,
                'issuer_id' => $issuerId,
                'tenant_id' => $tenantId,
                'cache_key' => $cacheKey,
                'keys_count' => count($keysToForget),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate GeneralSetting cache', [
                'error' => $e->getMessage(),
                'name' => $name,
                'issuer_id' => $issuerId,
                'tenant_id' => $tenantId,
            ]);
        }
    }

    private static function forgetCacheKeys(string $baseCacheKey, array $keys): void
    {
        Cache::forget($baseCacheKey);

        foreach ($keys as $key) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            Cache::forget($baseCacheKey.":{$key}");
        }
    }

    private static function payloadKeys($payload): array
    {
        if (is_array($payload)) {
            return array_keys($payload);
        }

        if (is_string($payload) && $payload !== '') {
            $decoded = json_decode($payload, true);
            if (is_array($decoded)) {
                return array_keys($decoded);
            }
        }

        return [];
    }

    /**
     * Limpa cache por tenant específico
     */
    public static function clearTenantCache(int $tenantId): void
    {
        try {
            $tag = self::CACHE_PREFIX."_tenant_{$tenantId}";
            Cache::tags([$tag])->flush();

            Log::info('GeneralSetting cache cleared for tenant', ['tenant_id' => $tenantId]);
        } catch (\Exception $e) {
            Log::error('Failed to clear tenant GeneralSetting cache', [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ]);
        }
    }

    /**
     * Limpa cache por issuer específico
     */
    public static function clearIssuerCache(int $issuerId): void
    {
        try {
            $tag = self::CACHE_PREFIX."_issuer_{$issuerId}";
            Cache::tags([$tag])->flush();

            Log::info('GeneralSetting cache cleared for issuer', ['issuer_id' => $issuerId]);
        } catch (\Exception $e) {
            Log::error('Failed to clear issuer GeneralSetting cache', [
                'error' => $e->getMessage(),
                'issuer_id' => $issuerId,
            ]);
        }
    }

    /**
     * Pré-carrega configurações frequentemente acessadas no cache
     */
    public static function warmCache(array $settingNames = [], ?int $issuerId = null, ?int $tenantId = null): void
    {
        if (empty($settingNames)) {
            $settingNames = ['configuracoes_gerais']; // Configurações padrão
        }

        try {
            foreach ($settingNames as $settingName) {
                self::getAll($settingName, $issuerId, $tenantId);
            }

            Log::info('GeneralSetting cache warmed', [
                'settings' => $settingNames,
                'issuer_id' => $issuerId,
                'tenant_id' => $tenantId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to warm GeneralSetting cache', [
                'error' => $e->getMessage(),
                'settings' => $settingNames,
                'issuer_id' => $issuerId,
                'tenant_id' => $tenantId,
            ]);
        }
    }

    /**
     * Obtém estatísticas de cache
     */
    public static function getCacheStats(): array
    {
        $driver = config('cache.default', 'file');
        $lastCleared = 'N/A';
        $cacheStatus = 'working';

        try {
            $lastCleared = Cache::get(self::CACHE_PREFIX.':last_cleared', 'Never');
        } catch (\Exception $e) {
            $cacheStatus = 'error';
            $lastCleared = 'Cache error: '.$e->getMessage();
        }

        return [
            'prefix' => self::CACHE_PREFIX,
            'ttl' => self::CACHE_TTL,
            'driver' => $driver,
            'status' => $cacheStatus,
            'last_cleared' => $lastCleared,
        ];
    }

    /**
     * Atualiza múltiplas configurações em batch
     */
    public static function setBatch(string $name, array $settings, ?int $issuerId = null, ?int $tenantId = null): self
    {
        return self::setValue($name, $settings, $issuerId, $tenantId);
    }

    /**
     * Remove uma configuração específica
     */
    public static function removeValue(string $name, string $key, ?int $issuerId = null, ?int $tenantId = null): bool
    {
        // Se não informado, pega tenant do usuário atual
        if (! $tenantId && Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }

        $query = static::where('name', $name);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($issuerId) {
            $query->where('issuer_id', $issuerId);
        }

        $setting = $query->first();

        if (! $setting || ! isset($setting->payload[$key])) {
            return false;
        }

        $payload = $setting->payload;
        unset($payload[$key]);

        $setting->update(['payload' => $payload]);

        return true;
    }

    /**
     * Verifica se uma configuração existe
     */
    public static function hasValue(string $name, string $key, ?int $issuerId = null, ?int $tenantId = null): bool
    {
        $value = self::getValue($name, $key, null, $issuerId, $tenantId);

        return $value !== null;
    }
}
