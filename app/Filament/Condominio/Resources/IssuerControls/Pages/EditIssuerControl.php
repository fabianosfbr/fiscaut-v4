<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use Filament\Resources\Pages\EditRecord;

class EditIssuerControl extends EditRecord
{
    protected static string $resource = IssuerControlResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
