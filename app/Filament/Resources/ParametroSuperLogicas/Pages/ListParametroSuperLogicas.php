<?php

namespace App\Filament\Resources\ParametroSuperLogicas\Pages;

use App\Filament\Resources\ParametroSuperLogicas\ParametroSuperLogicaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParametroSuperLogicas extends ListRecords
{
    protected static string $resource = ParametroSuperLogicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }

    public function getHeading(): string
    {
        return 'Parâmetros Super Lógica';
    }
}
