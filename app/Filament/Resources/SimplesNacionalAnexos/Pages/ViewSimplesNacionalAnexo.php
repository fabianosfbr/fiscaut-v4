<?php

namespace App\Filament\Resources\SimplesNacionalAnexos\Pages;

use App\Filament\Resources\SimplesNacionalAnexos\SimplesNacionalAnexoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSimplesNacionalAnexo extends ViewRecord
{
    protected static string $resource = SimplesNacionalAnexoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
