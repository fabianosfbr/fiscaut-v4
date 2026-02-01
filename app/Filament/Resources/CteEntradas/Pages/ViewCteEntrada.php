<?php

namespace App\Filament\Resources\CteEntradas\Pages;

use App\Filament\Actions\DownloadPdfCteAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Resources\CteEntradas\CteEntradaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

class ViewCteEntrada extends ViewRecord
{
    protected static string $resource = CteEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cte-list')
                ->label('Voltar para lista')
                ->color('gray')
                ->url(fn (): string => CteEntradaResource::getUrl('index')),

            ActionGroup::make([
                DownloadXmlAction::make(),
                DownloadPdfCteAction::make(),
            ])
                ->button()
                ->label('Download'),
        ];
    }
}
