<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Pages;

use App\Filament\Condominio\Resources\IssuerAssembleias\IssuerAssembleiaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIssuerAssembleia extends CreateRecord
{
    protected static string $resource = IssuerAssembleiaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = currentIssuer()->id;
        $data['tem_remuneracao'] = in_array('remuneracao', $data['tem_isencao_remuneracao']);
        $data['tem_isencao'] = in_array('isencao', $data['tem_isencao_remuneracao']);
        $data = IssuerAssembleiaResource::cleanData($data);

        return $data;
    }
}
