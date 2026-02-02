<?php

namespace App\Filament\Resources\NfseEntradas\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\NfseEntradas\NfseEntradaResource;

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
