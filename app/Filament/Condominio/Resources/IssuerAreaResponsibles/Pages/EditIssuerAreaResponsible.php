<?php

namespace App\Filament\Condominio\Resources\IssuerAreaResponsibles\Pages;

use App\Filament\Condominio\Resources\IssuerAreaResponsibles\IssuerAreaResponsibleResource;
use App\Models\Issuer;
use Filament\Resources\Pages\EditRecord;

class EditIssuerAreaResponsible extends EditRecord
{
    protected static string $resource = IssuerAreaResponsibleResource::class;

    protected static ?string $title = 'Editar';

    protected function getRedirectUrl(): string
    {
        // Redirecionar para a listagem de empresas
        return $this->getResource()::getUrl('index');
    }


}
