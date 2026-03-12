<?php

namespace App\Filament\Condominio\Resources\IssuerGroupControls\Pages;

use App\Filament\Condominio\Resources\IssuerGroupControls\IssuerGroupControlResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIssuerGroupControl extends CreateRecord
{
    protected static string $resource = IssuerGroupControlResource::class;


    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = currentIssuer()->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
