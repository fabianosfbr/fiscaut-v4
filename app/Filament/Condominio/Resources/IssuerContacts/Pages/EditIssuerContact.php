<?php

namespace App\Filament\Condominio\Resources\IssuerContacts\Pages;

use App\Filament\Condominio\Resources\IssuerContacts\IssuerContactResource;
use Filament\Resources\Pages\EditRecord;

class EditIssuerContact extends EditRecord
{
    protected static string $resource = IssuerContactResource::class;

    protected static ?string $title = 'Editar';

    public function mutateFormDataBeforeSave(array $data): array
    {
        $data['cpf'] = sanitize($data['cpf']);
        return $data;
    }
}
