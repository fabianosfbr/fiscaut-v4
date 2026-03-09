<?php

namespace App\Filament\Condominio\Pages;

use Filament\Pages\Page;
use UnitEnum;

class Overview extends Page
{
    protected string $view = 'filament.condominio.pages.overview';

    protected static string|UnitEnum|null $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = -1;

    protected static ?string $title = 'Visão Geral';
}
