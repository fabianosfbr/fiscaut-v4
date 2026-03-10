<?php

namespace App\Filament\Condominio\Resources\IssuerCondominios\Pages;

use App\Filament\Condominio\Resources\IssuerCondominios\IssuerCondominioResource;
use App\Filament\Resources\Issuers\Pages\CreateIssuer;

class CreateIssuerCondominio extends CreateIssuer
{
    protected static string $resource = IssuerCondominioResource::class;

    protected static ?string $title = 'Adicionar Novo';
}
