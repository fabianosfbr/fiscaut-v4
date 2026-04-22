<?php

namespace App\Filament\Condominio\Resources\SuperLogicaUnidades\Pages;

use App\Filament\Condominio\Resources\SuperLogicaUnidades\SuperLogicaUnidadeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuperLogicaUnidades extends ListRecords
{
    protected static string $resource = SuperLogicaUnidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
