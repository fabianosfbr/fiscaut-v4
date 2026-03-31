<?php

namespace App\Filament\Condominio\Resources\IssuerControlTypes\Pages;

use App\Filament\Condominio\Resources\IssuerControlTypes\IssuerControlTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuerControlTypes extends ListRecords
{
    protected static string $resource = IssuerControlTypeResource::class;

    protected static ?string $title = 'Tipos de Controle';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
