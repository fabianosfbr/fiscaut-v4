<?php

namespace App\Filament\Resources\NfeSaidas\Pages;

use App\Filament\Actions\DownloadPdfNfeAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Resources\NfeSaidas\NfeSaidaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

class ViewNfeSaida extends ViewRecord
{
    protected static string $resource = NfeSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('nfe-list')
                ->label('Voltar para lista')
                ->color('gray')
                ->url(fn (): string => NfeSaidaResource::getUrl('index')),
            ToggleEscrituracaoAction::make(),
            ActionGroup::make([
                DownloadXmlAction::make(),
                DownloadPdfNfeAction::make(),
            ])
                ->button()
                ->label('Download'),
        ];
    }
}
