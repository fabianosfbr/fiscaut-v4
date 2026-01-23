<?php

namespace App\Services;

use App\Models\Cfop;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CfopCacheService
{
    private const CACHE_KEY_PREFIX = 'cfop_config_';

    private const CACHE_TTL = 3600; // 1 hora

    /**
     * Obtém todos os CFOPs que compõem faturamento do cache
     *
     * @param  int  $issuerId  ID do emissor para distinguir o cache
     */
    public static function getCfopsFaturamento(int $issuerId): Collection
    {
        $cacheKey = self::CACHE_KEY_PREFIX.'faturamento_issuer_'.$issuerId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return Cfop::where('is_faturamento', true)
                ->select('codigo', 'descricao', 'anexo', 'is_faturamento')
                ->get()
                ->keyBy('codigo');
        });
    }

    /**
     * Obtém configuração de um CFOP específico do cache
     *
     * @param  string  $codigo  Código do CFOP
     * @param  int  $issuerId  ID do emissor para distinguir o cache
     */
    public static function getCfopConfig(string $codigo, int $issuerId): ?Cfop
    {
        $cfopsFaturamento = self::getCfopsFaturamento($issuerId);

        return $cfopsFaturamento->get($codigo);
    }

    /**
     * Verifica se um CFOP compõe faturamento
     *
     * @param  string  $codigo  Código do CFOP
     * @param  int  $issuerId  ID do emissor para distinguir o cache
     */
    public static function isCfopFaturamento(string $codigo, int $issuerId): bool
    {
        $cfop = self::getCfopConfig($codigo, $issuerId);

        return $cfop ? $cfop->is_faturamento : false;
    }

    /**
     * Obtém todos os códigos de CFOP que compõem faturamento
     *
     * @param  int  $issuerId  ID do emissor para distinguir o cache
     */
    public static function getCodigosCfopFaturamento(int $issuerId): array
    {
        return self::getCfopsFaturamento($issuerId)->keys()->toArray();
    }

    /**
     * Limpa o cache de CFOPs para um emissor específico
     *
     * @param  int|null  $issuerId  ID do emissor. Se null, limpa todos os caches de CFOP
     */
    public static function clearCache(?int $issuerId = null): void
    {
        if ($issuerId) {
            $cacheKey = self::CACHE_KEY_PREFIX.'faturamento_issuer_'.$issuerId;
            Cache::forget($cacheKey);
        } else {
            // Limpa todos os caches de CFOP (útil quando um CFOP é alterado globalmente)
            $pattern = self::CACHE_KEY_PREFIX.'faturamento_issuer_*';
            // Como não temos acesso direto ao padrão no Laravel Cache,
            // vamos usar uma abordagem mais simples para limpar tudo
            Cache::flush(); // Alternativa: implementar lógica específica se necessário
        }
    }

    /**
     * Pré-carrega o cache de CFOPs para um emissor
     *
     * @param  int  $issuerId  ID do emissor
     */
    public static function warmCache(int $issuerId): void
    {
        self::getCfopsFaturamento($issuerId);
    }

    /**
     * Obtém estatísticas do cache de CFOPs para um emissor
     *
     * @param  int  $issuerId  ID do emissor
     */
    public static function getCacheStats(int $issuerId): array
    {
        $cfopsFaturamento = self::getCfopsFaturamento($issuerId);

        return [
            'issuer_id' => $issuerId,
            'total_cfops_faturamento' => $cfopsFaturamento->count(),
            'codigos_cfops' => $cfopsFaturamento->keys()->toArray(),
            'cache_key' => self::CACHE_KEY_PREFIX.'faturamento_issuer_'.$issuerId,
            'cache_ttl' => self::CACHE_TTL,
        ];
    }
}
