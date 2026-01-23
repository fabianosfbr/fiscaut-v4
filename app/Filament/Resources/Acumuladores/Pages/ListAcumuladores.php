<?php

namespace App\Filament\Resources\Acumuladores\Pages;

use App\Filament\Resources\Acumuladores\AcumuladoresResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAcumuladores extends ListRecords
{
    protected static string $resource = AcumuladoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
