<?php

namespace App\Filament\Condominio\Resources\Manutencaos\Pages;

use App\Filament\Condominio\Resources\Manutencaos\ManutencaoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditManutencao extends EditRecord
{
    protected static string $resource = ManutencaoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
