<?php

namespace App\Filament\Resources\NfseEntradas\Pages;

use App\Filament\Resources\NfseEntradas\NfseEntradaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNfseEntrada extends EditRecord
{
    protected static string $resource = NfseEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
