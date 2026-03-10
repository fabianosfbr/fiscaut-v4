<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Pages;

use App\Filament\Condominio\Resources\IssuerDocuments\IssuerDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIssuerDocument extends EditRecord
{
    protected static string $resource = IssuerDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
