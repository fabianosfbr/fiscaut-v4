<?php

namespace App\Filament\Condominio\Pages;

use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaConselhoMandatoOverview;
use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaPrazoTecnicoOverview;
use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaSindicoMandatoOverview;
use App\Filament\Condominio\Resources\IssuerControl\Widgets\IssuerControlVencimentosOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class DashboardPorEmpresa extends BaseDashboard
{
    protected static ?string $title = 'Visão Por Empresa';

    protected function getHeaderWidgets(): array
    {
        return [
            IssuerControlVencimentosOverview::class,
            IssuerAssembleiaPrazoTecnicoOverview::class,
            IssuerAssembleiaSindicoMandatoOverview::class,
            IssuerAssembleiaConselhoMandatoOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 4,
        ];
    }
}
