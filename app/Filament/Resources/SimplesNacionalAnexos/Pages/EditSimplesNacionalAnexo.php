<?php

namespace App\Filament\Resources\SimplesNacionalAnexos\Pages;

use App\Filament\Resources\SimplesNacionalAnexos\SimplesNacionalAnexoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSimplesNacionalAnexo extends EditRecord
{
    protected static string $resource = SimplesNacionalAnexoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
