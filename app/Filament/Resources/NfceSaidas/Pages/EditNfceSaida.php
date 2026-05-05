<?php

namespace App\Filament\Resources\NfceSaidas\Pages;

use App\Filament\Resources\NfceSaidas\NfceSaidaResource;
use Filament\Resources\Pages\EditRecord;

class EditNfceSaida extends EditRecord
{
    protected static string $resource = NfceSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
