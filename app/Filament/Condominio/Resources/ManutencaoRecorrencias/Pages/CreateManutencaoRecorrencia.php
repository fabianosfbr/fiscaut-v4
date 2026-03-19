<?php

namespace App\Filament\Condominio\Resources\ManutencaoRecorrencias\Pages;

use App\Filament\Condominio\Resources\ManutencaoRecorrencias\ManutencaoRecorrenciaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateManutencaoRecorrencia extends CreateRecord
{
    protected static string $resource = ManutencaoRecorrenciaResource::class;


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
