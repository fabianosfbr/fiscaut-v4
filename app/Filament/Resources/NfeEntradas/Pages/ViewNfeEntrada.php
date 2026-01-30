<?php

namespace App\Filament\Resources\NfeEntradas\Pages;

use App\Filament\Actions\ClassificarDocumentoNfeAvancadoAction;
use App\Filament\Actions\DownloadPdfNfeAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Resources\NfeEntradas\NfeEntradaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

class ViewNfeEntrada extends ViewRecord
{
    protected static string $resource = NfeEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('nfe-list')
                ->label('Voltar para lista')
                ->color('gray')
                ->url(fn (): string => NfeEntradaResource::getUrl('index')),
            ToggleEscrituracaoAction::make(),
            ClassificarDocumentoNfeAvancadoAction::make(),
            ActionGroup::make([
                DownloadXmlAction::make(),
                DownloadPdfNfeAction::make(),
            ])
                ->button()
                ->label('Download'),

        ];
    }

    public function getHeading(): string
    {
        return 'NFe de Entrada';
    }
}
