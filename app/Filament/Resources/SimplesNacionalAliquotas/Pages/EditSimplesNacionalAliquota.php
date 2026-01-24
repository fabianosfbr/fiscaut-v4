<?php

namespace App\Filament\Resources\SimplesNacionalAliquotas\Pages;

use App\Filament\Resources\SimplesNacionalAliquotas\SimplesNacionalAliquotaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSimplesNacionalAliquota extends EditRecord
{
    protected static string $resource = SimplesNacionalAliquotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
