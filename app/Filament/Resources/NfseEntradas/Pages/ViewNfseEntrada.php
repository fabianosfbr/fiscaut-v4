<?php

namespace App\Filament\Resources\NfseEntradas\Pages;

use App\Filament\Resources\NfseEntradas\NfseEntradaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNfseEntrada extends ViewRecord
{
    protected static string $resource = NfseEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
