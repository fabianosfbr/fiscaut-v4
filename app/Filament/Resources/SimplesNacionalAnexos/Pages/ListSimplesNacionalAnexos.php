<?php

namespace App\Filament\Resources\SimplesNacionalAnexos\Pages;

use App\Filament\Resources\SimplesNacionalAnexos\SimplesNacionalAnexoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSimplesNacionalAnexos extends ListRecords
{
    protected static string $resource = SimplesNacionalAnexoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
