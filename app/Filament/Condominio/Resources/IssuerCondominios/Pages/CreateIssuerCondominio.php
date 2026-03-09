<?php

namespace App\Filament\Condominio\Resources\IssuerCondominios\Pages;

use App\Filament\Condominio\Resources\IssuerCondominios\IssuerCondominioResource;
use Filament\Resources\Pages\CreateRecord;


class CreateIssuerCondominio extends CreateRecord
{
    protected static string $resource = IssuerCondominioResource::class;

    protected static ?string $title = 'Adicionar Novo';


}
