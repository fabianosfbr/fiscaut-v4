<?php

namespace App\Filament\Resources\NfceSaidas\Pages;

use App\Filament\Resources\NfceSaidas\NfceSaidaResource;
use Filament\Resources\Pages\ListRecords;

class ListNfceSaidas extends ListRecords
{
    protected static string $resource = NfceSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
