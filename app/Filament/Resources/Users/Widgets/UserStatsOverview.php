<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userQuery = User::query();

        return [
            Stat::make('Total de usuários', $userQuery->count()),
            Stat::make('Usuários ativos', $userQuery->where('status', 'active')->count()),
            Stat::make('Usuários inativos', $userQuery->where('status', 'inactive')->count()),
        ];
    }
}
