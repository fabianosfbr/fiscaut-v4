<?php

namespace App\Filament\Condominio\Resources\IssuerAreaResponsibles\Pages;

use App\Filament\Condominio\Resources\IssuerAreaResponsibles\IssuerAreaResponsibleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuerAreaResponsibles extends ListRecords
{
    protected static string $resource = IssuerAreaResponsibleResource::class;

    protected static ?string $title = 'Responsáveis por Área';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
