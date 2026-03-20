<?php

namespace App\Filament\Condominio\Resources\Manutencaos\Pages;


use App\Filament\Condominio\Resources\Manutencaos\ManutencaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManutencaos extends ListRecords
{
    protected static string $resource = ManutencaoResource::class;


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Nova'),
        ];
    }
}
