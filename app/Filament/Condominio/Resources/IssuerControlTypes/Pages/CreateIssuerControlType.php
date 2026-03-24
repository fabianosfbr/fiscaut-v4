<?php

namespace App\Filament\Condominio\Resources\IssuerControlTypes\Pages;

use App\Filament\Condominio\Resources\IssuerControlTypes\IssuerControlTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIssuerControlType extends CreateRecord
{
    protected static string $resource = IssuerControlTypeResource::class;

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
