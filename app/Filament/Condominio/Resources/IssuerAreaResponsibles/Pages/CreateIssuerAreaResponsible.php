<?php

namespace App\Filament\Condominio\Resources\IssuerAreaResponsibles\Pages;

use App\Filament\Condominio\Resources\IssuerAreaResponsibles\IssuerAreaResponsibleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIssuerAreaResponsible extends CreateRecord
{
    protected static string $resource = IssuerAreaResponsibleResource::class;

    protected static ?string $title = 'Adicionar Novo';

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = currentIssuer()->id;
        $data['tenant_id'] = currentIssuer()->tenant_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Redirecionar para a listagem de empresas
        return $this->getResource()::getUrl('index');
    }
}
