<?php

namespace App\Models;

use App\Enums\SimplesNacionalReceitaEnum;
use App\Services\CfopCacheService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Cfop extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'anexo' => SimplesNacionalReceitaEnum::class,
        'is_faturamento' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function (Cfop $cfop) {
            self::clearAllCfopCache('saved', $cfop);
        });

        static::deleted(function (Cfop $cfop) {
            self::clearAllCfopCache('deleted', $cfop);
        });

        static::updated(function (Cfop $cfop) {
            self::clearAllCfopCache('updated', $cfop);
        });
    }

    /**
     * Limpa todo o cache de CFOPs e registra a ação
     *
     * @param  string  $action  Ação que disparou a limpeza
     * @param  Cfop  $cfop  Modelo do CFOP alterado
     */
    private static function clearAllCfopCache(string $action, Cfop $cfop): void
    { // Limpa todo o cache de CFOPs (todos os emissores)
        try {
            // Limpa todo o cache de CFOPs (todos os emissores)
            CfopCacheService::clearCache();

            Log::info('Cache de CFOPs limpo automaticamente', [
                'action' => $action,
                'cfop_id' => $cfop->id,
                'cfop_codigo' => $cfop->codigo,
                'cfop_descricao' => $cfop->descricao ?? 'N/A',
                'is_faturamento' => $cfop->is_faturamento ?? false,
                'anexo' => $cfop->anexo?->value ?? null,
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao limpar cache de CFOPs automaticamente', [
                'action' => $action,
                'cfop_id' => $cfop->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
