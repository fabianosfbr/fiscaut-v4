<?php

namespace App\Filament\Condominio\Resources\IssuerControlRecorrencies\Pages;

use App\Filament\Condominio\Resources\IssuerControlRecorrencies\IssuerControlRecorrencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIssuerControlRecorrency extends EditRecord
{
    protected static string $resource = IssuerControlRecorrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
