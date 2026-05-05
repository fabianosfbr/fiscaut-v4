<?php

namespace App\Filament\Resources\NfceSaidas\Pages;

use App\Filament\Resources\NfceSaidas\NfceSaidaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNfceSaida extends ViewRecord
{
    protected static string $resource = NfceSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
