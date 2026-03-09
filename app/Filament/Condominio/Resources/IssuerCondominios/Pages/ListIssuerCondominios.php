<?php

namespace App\Filament\Condominio\Resources\IssuerCondominios\Pages;

use App\Filament\Condominio\Resources\IssuerCondominios\IssuerCondominioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuerCondominios extends ListRecords
{
    protected static string $resource = IssuerCondominioResource::class;

    protected static ?string $title = 'Empresas';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Nova'),
        ];
    }
}
