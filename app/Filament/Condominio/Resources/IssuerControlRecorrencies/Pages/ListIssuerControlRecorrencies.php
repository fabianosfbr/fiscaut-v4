<?php

namespace App\Filament\Condominio\Resources\IssuerControlRecorrencies\Pages;

use App\Filament\Condominio\Resources\IssuerControlRecorrencies\IssuerControlRecorrencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuerControlRecorrencies extends ListRecords
{
    protected static string $resource = IssuerControlRecorrencyResource::class;

    protected static ?string $title = 'Recorrências';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
