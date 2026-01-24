<?php

namespace App\Filament\Resources\SimplesNacionalAliquotas\Pages;

use App\Filament\Resources\SimplesNacionalAliquotas\SimplesNacionalAliquotaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSimplesNacionalAliquotas extends ListRecords
{
    protected static string $resource = SimplesNacionalAliquotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
