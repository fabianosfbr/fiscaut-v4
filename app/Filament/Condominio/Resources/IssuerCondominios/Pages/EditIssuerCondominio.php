<?php

namespace App\Filament\Condominio\Resources\IssuerCondominios\Pages;

use App\Filament\Condominio\Resources\IssuerCondominios\IssuerCondominioResource;
use Filament\Resources\Pages\EditRecord;

class EditIssuerCondominio extends EditRecord
{
    protected static string $resource = IssuerCondominioResource::class;

    protected static ?string $title = 'Editar';

   
}
