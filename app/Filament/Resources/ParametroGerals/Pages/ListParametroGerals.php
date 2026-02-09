<?php

namespace App\Filament\Resources\ParametroGerals\Pages;

use App\Filament\Resources\ParametroGerals\ParametroGeralResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParametroGerals extends ListRecords
{
    protected static string $resource = ParametroGeralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Adicionar Novo'),
        ];
    }

     public function getHeading(): string
    {
        return 'Parâmetros Gerais';
    }

    
}
