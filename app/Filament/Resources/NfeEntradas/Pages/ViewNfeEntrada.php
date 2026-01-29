<?php

namespace App\Filament\Resources\NfeEntradas\Pages;

use App\Filament\Resources\NfeEntradas\NfeEntradaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNfeEntrada extends ViewRecord
{
    protected static string $resource = NfeEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
