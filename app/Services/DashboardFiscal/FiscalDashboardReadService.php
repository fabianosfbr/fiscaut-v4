<?php

namespace App\Services\DashboardFiscal;

use App\Models\StatisticIssuer;
use Illuminate\Support\Facades\Cache;

class FiscalDashboardReadService
{
    public function getMonthlyKpis(int $tenantId, string $issuerCnpj, string $monthKey): array
    {
        $cacheKey = "dashboard_fiscal:kpis:tenant={$tenantId}:issuer={$issuerCnpj}:month={$monthKey}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($tenantId, $issuerCnpj, $monthKey) {
            $rows = StatisticIssuer::query()
                ->where('tenant_id', $tenantId)
                ->where('issuer', $issuerCnpj)
                ->where('periodo', 'mensal')
                ->where('data', $monthKey)
                ->whereIn('doc_tipo', ['nfe', 'cte', 'nfse'])
                ->get(['doc_tipo', 'tipo', 'valor']);

            $indexed = $rows->keyBy(fn (StatisticIssuer $row) => "{$row->doc_tipo}|{$row->tipo}");

            return [
                'nfe_saida' => (float) ($indexed->get('nfe|saida')?->valor ?? 0),
                'nfe_entrada' => (float) ($indexed->get('nfe|entrada')?->valor ?? 0),
                'cte_emitido' => (float) ($indexed->get('cte|saida')?->valor ?? 0),
                'cte_tomador' => (float) ($indexed->get('cte|tomador')?->valor ?? 0),
                'cte_entrada' => (float) ($indexed->get('cte|entrada')?->valor ?? 0),
                'nfse_tomador' => (float) ($indexed->get('nfse|tomador')?->valor ?? 0),
            ];
        });
    }

    public function getMonthlySeries(int $tenantId, string $issuerCnpj, array $monthKeys): array
    {
        $monthKeys = array_values(array_unique(array_filter($monthKeys)));
        sort($monthKeys);

        $from = $monthKeys[0] ?? null;
        $to = $monthKeys[count($monthKeys) - 1] ?? null;

        $cacheKey = "dashboard_fiscal:series:tenant={$tenantId}:issuer={$issuerCnpj}:from={$from}:to={$to}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($tenantId, $issuerCnpj, $monthKeys) {
            if ($monthKeys === []) {
                return [];
            }

            $rows = StatisticIssuer::query()
                ->where('tenant_id', $tenantId)
                ->where('issuer', $issuerCnpj)
                ->where('periodo', 'mensal')
                ->whereIn('data', $monthKeys)
                ->whereIn('doc_tipo', ['nfe', 'cte', 'nfse'])
                ->get(['data', 'doc_tipo', 'tipo', 'valor']);

            $result = [];

            foreach ($monthKeys as $monthKey) {
                $result[$monthKey] = [
                    'nfe_saida' => 0.0,
                    'nfe_entrada' => 0.0,
                    'cte_emitido' => 0.0,
                    'cte_tomado' => 0.0,
                    'nfse_tomada' => 0.0,
                ];
            }

            foreach ($rows as $row) {
                $key = match ("{$row->doc_tipo}|{$row->tipo}") {
                    'nfe|saida' => 'nfe_saida',
                    'nfe|entrada' => 'nfe_entrada',
                    'cte|saida' => 'cte_emitido',
                    'cte|entrada' => 'cte_tomado',
                    'nfse|tomador' => 'nfse_tomada',
                    default => null,
                };

                if ($key === null) {
                    continue;
                }

                $monthKey = (string) $row->data;

                if (! array_key_exists($monthKey, $result)) {
                    continue;
                }

                $result[$monthKey][$key] = (float) $row->valor;
            }

            return $result;
        });
    }
}

