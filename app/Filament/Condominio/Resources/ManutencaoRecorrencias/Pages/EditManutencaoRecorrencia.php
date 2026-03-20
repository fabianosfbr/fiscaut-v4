<?php

namespace App\Filament\Condominio\Resources\ManutencaoRecorrencias\Pages;

use App\Filament\Condominio\Resources\ManutencaoRecorrencias\ManutencaoRecorrenciaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManutencaoRecorrencia extends EditRecord
{
    protected static string $resource = ManutencaoRecorrenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
