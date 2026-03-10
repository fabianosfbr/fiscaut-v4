<?php

namespace App\Filament\Condominio\Resources\IssuerContacts\Pages;

use App\Filament\Condominio\Resources\IssuerContacts\IssuerContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIssuerContact extends CreateRecord
{
    protected static string $resource = IssuerContactResource::class;

    protected static ?string $title = 'Adicionar Novo';

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = currentIssuer()->id;
        $data['tenant_id'] = currentIssuer()->tenant_id;
        $data['cpf'] = sanitize($data['cpf']);

        return $data;
    }
}
