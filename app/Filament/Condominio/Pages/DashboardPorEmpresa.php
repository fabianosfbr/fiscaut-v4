<?php

namespace App\Filament\Condominio\Pages;

use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaConselhoMandatoOverview;
use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaPrazoTecnicoOverview;
use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaSindicoMandatoOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class DashboardPorEmpresa extends BaseDashboard
{
    protected static ?string $title = 'Visão Por Empresa';

    protected function getHeaderWidgets(): array
    {
        return [
            IssuerAssembleiaPrazoTecnicoOverview::class,
            IssuerAssembleiaSindicoMandatoOverview::class,
            IssuerAssembleiaConselhoMandatoOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return [
            'default' => 1,
            'lg' => 3,
        ];
    }
}
