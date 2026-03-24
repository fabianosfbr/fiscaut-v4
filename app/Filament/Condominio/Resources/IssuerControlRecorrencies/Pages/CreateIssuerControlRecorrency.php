<?php

namespace App\Filament\Condominio\Resources\IssuerControlRecorrencies\Pages;

use App\Filament\Condominio\Resources\IssuerControlRecorrencies\IssuerControlRecorrencyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIssuerControlRecorrency extends CreateRecord
{
    protected static string $resource = IssuerControlRecorrencyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = currentIssuer()->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
