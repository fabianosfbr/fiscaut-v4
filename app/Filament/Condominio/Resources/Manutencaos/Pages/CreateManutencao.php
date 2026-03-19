<?php

namespace App\Filament\Condominio\Resources\Manutencaos\Pages;

use App\Filament\Condominio\Resources\Manutencaos\ManutencaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateManutencao extends CreateRecord
{
    protected static string $resource = ManutencaoResource::class;

    protected static ?string $title = 'Nova Manutenção';

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
