<?php

namespace App\Filament\Resources\LogSefazCteContents\Widgets;

use App\Models\LogSefazCteContent;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MinMaxNsuCteOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $currentIssuer = currentIssuer();

        $max = LogSefazCteContent::where('issuer_id', $currentIssuer->id)
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->max('max_nsu');

        $min = LogSefazCteContent::where('issuer_id', $currentIssuer->id)
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->min('nsu');

        $count = 0;

        if (isset($max) and isset($min)) {
            $nsus = LogSefazCteContent::where('issuer_id', $currentIssuer->id)
                ->whereBetween('nsu', [$min, $max])
                ->get()->pluck('nsu', 'id');
            for ($nsu = $min; $nsu < $max; $nsu++) {
                if (! $nsus->contains($nsu)) {
                    $count++;
                }
            }
        }

        return [
            Stat::make('Mínimo NSU', $min ?? 0),
            Stat::make('Máximo NSU', $max ?? 0),
            Stat::make('Qtde de NSU Ausentes', $count ?? 0),
        ];
    }
}
