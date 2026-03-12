<?php

namespace App\Filament\Condominio\Resources\IssuerGroupControls\Pages;

use App\Filament\Condominio\Resources\IssuerGroupControls\IssuerGroupControlResource;
use Filament\Resources\Pages\EditRecord;

class EditIssuerGroupControl extends EditRecord
{
    protected static string $resource = IssuerGroupControlResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
