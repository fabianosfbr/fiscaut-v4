<?php

namespace App\Filament\Condominio\Resources\TipoManutencaos\Pages;

use App\Filament\Condominio\Resources\TipoManutencaos\TipoManutencaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTipoManutencao extends CreateRecord
{
    protected static string $resource = TipoManutencaoResource::class;

    protected static ?string $title = 'Novo Tipo de Controle';

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
