<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Pages;

use App\Filament\Condominio\Resources\IssuerAssembleias\IssuerAssembleiaResource;
use Filament\Resources\Pages\EditRecord;

class EditIssuerAssembleia extends EditRecord
{
    protected static string $resource = IssuerAssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['tem_remuneracao'] = in_array('remuneracao', $data['tem_isencao_remuneracao']);
        $data['tem_isencao'] = in_array('isencao', $data['tem_isencao_remuneracao']);
        $data = IssuerAssembleiaResource::cleanData($data);

        return $data;
    }
}
