<?php

namespace App\Filament\Resources\NcmRestricoes\Pages;

use App\Filament\Resources\NcmRestricoes\NcmRestricaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNcmRestricoes extends ListRecords
{
    protected static string $resource = NcmRestricaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Nova Restrição'),
        ];
    }
}
