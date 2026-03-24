<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Pages;

use App\Filament\Condominio\Resources\IssuerDocuments\IssuerDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListIssuerDocuments extends ListRecords
{
    protected static string $resource = IssuerDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo')
                ->modalHeading('Adicionar Novo'),
        ];
    }
}
