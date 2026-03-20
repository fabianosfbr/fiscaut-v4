<?php

namespace App\Filament\Condominio\Resources\ManutencaoHistoricos\Pages;

use App\Filament\Condominio\Resources\ManutencaoHistoricos\ManutencaoHistoricoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManutencaoHistoricos extends ListRecords
{
    protected static string $resource = ManutencaoHistoricoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
