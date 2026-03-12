<?php

namespace App\Filament\Condominio\Resources\IssuerAges\Pages;

use App\Filament\Condominio\Resources\IssuerAges\IssuerAgeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIssuerAge extends EditRecord
{
    protected static string $resource = IssuerAgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
