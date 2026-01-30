<?php

namespace App\Filament\Resources\NfeSaidas\Pages;

use App\Filament\Resources\NfeSaidas\NfeSaidaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNfeSaida extends ViewRecord
{
    protected static string $resource = NfeSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
