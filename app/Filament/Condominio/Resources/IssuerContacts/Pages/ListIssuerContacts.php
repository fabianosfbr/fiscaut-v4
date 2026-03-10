<?php

namespace App\Filament\Condominio\Resources\IssuerContacts\Pages;

use App\Filament\Condominio\Resources\IssuerContacts\IssuerContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuerContacts extends ListRecords
{
    protected static string $resource = IssuerContactResource::class;

    protected static ?string $title = 'Contatos';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
