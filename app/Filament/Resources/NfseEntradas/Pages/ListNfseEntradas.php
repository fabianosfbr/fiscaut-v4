<?php

namespace App\Filament\Resources\NfseEntradas\Pages;

use App\Filament\Resources\NfseEntradas\NfseEntradaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListNfseEntradas extends ListRecords
{
    protected static string $resource = NfseEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return 'Notas Fiscais Serviços';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
