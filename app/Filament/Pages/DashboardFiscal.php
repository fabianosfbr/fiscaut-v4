<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;

class DashboardFiscal extends BaseDashboard
{
    // protected string $view = 'filament.pages.dashboard-contabil';

    protected static string $routePath = '/';

    protected static ?string $title = 'Painel Fiscal';

    protected static ?int $navigationSort = 1;
}
