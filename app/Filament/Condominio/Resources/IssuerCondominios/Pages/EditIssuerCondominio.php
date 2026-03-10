<?php

namespace App\Filament\Condominio\Resources\IssuerCondominios\Pages;

use App\Filament\Condominio\Resources\IssuerCondominios\IssuerCondominioResource;
use App\Filament\Resources\Issuers\Pages\EditIssuer;

class EditIssuerCondominio extends EditIssuer
{
    protected static string $resource = IssuerCondominioResource::class;

    protected static ?string $title = 'Editar';
}
