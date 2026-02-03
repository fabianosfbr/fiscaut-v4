<?php

namespace App\Filament\Widgets;

use App\Services\DashboardFiscal\FiscalDashboardReadService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FiscalDashboardKpis extends StatsOverviewWidget
{
    // protected ?string $heading = 'Documentos do mês';

    protected function getStats(): array
    {
        $user = Auth::user();

        if ($user === null || $user->currentIssuer === null) {
            return [
                Stat::make('NFe Saída', 0)
                    ->chart([0, 0, 0, 0, 0, 0])
                    ->color('success'),
                Stat::make('NFe Entrada', 0)
                    ->chart([0, 0, 0, 0, 0, 0])
                    ->color('success'),
                Stat::make('CTe Emitidos', 0)
                    ->chart([0, 0, 0, 0, 0, 0])
                    ->color('success'),
                Stat::make('CTe Tomados', 0)
                    ->chart([0, 0, 0, 0, 0, 0])
                    ->color('success'),
                Stat::make('NFS-e Tomadas', 0)
                    ->chart([0, 0, 0, 0, 0, 0])
                    ->color('success'),
            ];
        }

        $monthKey = now()->format('Y-m');

        $kpis = app(FiscalDashboardReadService::class)->getMonthlyKpis(
            tenantId: (int) $user->tenant_id,
            issuerCnpj: (string) $user->currentIssuer->cnpj,
            monthKey: $monthKey,
        );

        return [
            Stat::make('NFe Saída', (int) $kpis['nfe_saida'])
                ->chart([0, 0, 0, 0, 0, 0])
                ->color('success'),
            Stat::make('NFe Entrada', (int) $kpis['nfe_entrada'])
                ->chart([0, 0, 0, 0, 0, 0])
                ->color('success'),
            Stat::make('NFS-e Tomada', (int) $kpis['nfse_tomador'])
                ->chart([0, 0, 0, 0, 0, 0])
                ->color('success'),
            Stat::make('CTe Entrada', (int) $kpis['cte_entrada'])
                ->chart([0, 0, 0, 0, 0, 0])
                ->color('success'),
            Stat::make('CTe Saida', (int) $kpis['cte_emitido'])
                ->chart([0, 0, 0, 0, 0, 0])
                ->color('success'),
            Stat::make('CTe Tomada', (int) $kpis['cte_tomador'])
                ->chart([0, 0, 0, 0, 0, 0])
                ->color('success'),
        ];
    }
}
