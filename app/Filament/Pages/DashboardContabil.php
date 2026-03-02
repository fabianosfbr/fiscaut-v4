<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;

class DashboardContabil extends BaseDashboard
{
    // protected string $view = 'filament.pages.dashboard-contabil';

    protected static string $routePath = 'contabil';

    protected static ?string $title = 'Painel Contábil';

    protected static ?int $navigationSort = 2;
}
