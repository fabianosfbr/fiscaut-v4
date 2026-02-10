<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals\Widgets;

use App\Models\ImportarLancamentoContabil;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ImportLancamentoOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $resultados = ImportarLancamentoContabil::where('issuer_id', $user->currentIssuer->id)
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) as total_registros')
            ->selectRaw('SUM(CASE WHEN is_exist = 1 THEN 1 ELSE 0 END) as total_vinculados')
            ->selectRaw('SUM(CASE WHEN is_exist = 0 THEN 1 ELSE 0 END) as total_desvinculados')
            ->first();

        return [
            Stat::make('Nº registros importados', $resultados->total_registros),
            Stat::make('Nº registros com vínculo', $resultados->total_vinculados ?? 0),
            Stat::make('Nº registros sem vínculo', $resultados->total_desvinculados ?? 0),
        ];
    }
}
