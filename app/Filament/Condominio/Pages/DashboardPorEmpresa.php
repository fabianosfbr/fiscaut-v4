<?php

namespace App\Filament\Condominio\Pages;

use Filament\Pages\Page;
use UnitEnum;

class DashboardPorEmpresa extends Page
{
    protected string $view = 'filament.condominio.pages.dashboard-por-empresa';

    protected static string|UnitEnum|null $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Visão Por Empresa';
}
