<?php

namespace App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Widgets;

use App\Models\ImportarLancamentoContabil;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ImportLancamentoSuperLogicaOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $resultados = ImportarLancamentoContabil::where('issuer_id', $user->currentIssuer->id)
            ->where('user_id', $user->id)
            ->whereJsonContains('metadata->type', 'super_logica')
            ->selectRaw('COUNT(*) as total_registros')
            ->selectRaw('SUM(CASE WHEN is_exist = 1 THEN 1 ELSE 0 END) as total_vinculados')
            ->selectRaw('SUM(CASE WHEN is_exist = 0 THEN 1 ELSE 0 END) as total_desvinculados')
            ->first();

        return [
            Stat::make('Nº registros importados', $resultados->total_registros)
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Nº registros com vínculo', $resultados->total_vinculados ?? 0)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
            Stat::make('Nº registros sem vínculo', $resultados->total_desvinculados ?? 0)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
        ];
    }
}
