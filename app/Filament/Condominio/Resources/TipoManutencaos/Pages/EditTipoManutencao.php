<?php

namespace App\Filament\Condominio\Resources\TipoManutencaos\Pages;

use App\Filament\Condominio\Resources\TipoManutencaos\TipoManutencaoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipoManutencao extends EditRecord
{
    protected static string $resource = TipoManutencaoResource::class;

    protected static ?string $title = 'Editar Tipo de Controle';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
