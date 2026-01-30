<?php

namespace App\Filament\Resources\NfeSaidas\Pages;

use App\Filament\Resources\NfeSaidas\NfeSaidaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListNfeSaidas extends ListRecords
{
    protected static string $resource = NfeSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return 'Notas Fiscais Eletrônicas';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
