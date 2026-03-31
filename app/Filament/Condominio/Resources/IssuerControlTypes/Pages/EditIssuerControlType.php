<?php

namespace App\Filament\Condominio\Resources\IssuerControlTypes\Pages;

use App\Filament\Condominio\Resources\IssuerControlTypes\IssuerControlTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditIssuerControlType extends EditRecord
{
    protected static string $resource = IssuerControlTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
