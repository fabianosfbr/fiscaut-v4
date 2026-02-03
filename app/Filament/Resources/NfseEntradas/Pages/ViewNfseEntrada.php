<?php

namespace App\Filament\Resources\NfseEntradas\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Resources\NfseEntradas\NfseEntradaResource;

class ViewNfseEntrada extends ViewRecord
{
    protected static string $resource = NfseEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('nfe-list')
                ->label('Voltar para lista')
                ->color('gray')
                ->url(fn (): string => NfseEntradaResource::getUrl('index')),
            ToggleEscrituracaoAction::make(),
        ];
    }
}
