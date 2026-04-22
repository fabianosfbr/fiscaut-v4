<?php

namespace App\Filament\Condominio\Resources\SuperLogicaUnidades\Pages;

use App\Filament\Condominio\Resources\SuperLogicaUnidades\SuperLogicaUnidadeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSuperLogicaUnidade extends EditRecord
{
    protected static string $resource = SuperLogicaUnidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
