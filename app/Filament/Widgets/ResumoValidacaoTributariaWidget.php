<?php

namespace App\Filament\Widgets;

use App\Models\NfeValidacaoTributaria;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumoValidacaoTributariaWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $issuer = currentIssuer();

        $pendentes = NfeValidacaoTributaria::query()
            ->where('issuer_id', $issuer->id)
            ->where('status', 'pendente')
            ->count();

        $confirmados = NfeValidacaoTributaria::query()
            ->where('issuer_id', $issuer->id)
            ->where('status', 'confirmado')
            ->count();

        $total = NfeValidacaoTributaria::query()
            ->where('issuer_id', $issuer->id)
            ->count();

        $nfeComPendencias = NfeValidacaoTributaria::query()
            ->where('issuer_id', $issuer->id)
            ->where('status', 'pendente')
            ->distinct('nfe_id')
            ->count('nfe_id');

        return [
            Stat::make('Inconsistências Pendentes', $pendentes)
                ->description("Em {$nfeComPendencias} NF-e")
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Confirmados', $confirmados)
                ->description('Resolvidos')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Total de Validações', $total)
                ->description('Acumulado')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('info'),

            Stat::make('NF-e sem Pendências', '—')
                ->description('Acesse os painéis de NF-e')
                ->descriptionIcon('heroicon-o-arrow-right')
                ->color('gray'),
        ];
    }
}
