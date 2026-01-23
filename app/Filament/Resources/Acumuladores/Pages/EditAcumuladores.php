<?php

namespace App\Filament\Resources\Acumuladores\Pages;

use App\Filament\Resources\Acumuladores\AcumuladoresResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAcumuladores extends EditRecord
{
    protected static string $resource = AcumuladoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
