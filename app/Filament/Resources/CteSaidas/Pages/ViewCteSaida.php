<?php

namespace App\Filament\Resources\CteSaidas\Pages;

use App\Filament\Actions\DownloadPdfCteAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Resources\CteSaidas\CteSaidaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

class ViewCteSaida extends ViewRecord
{
    protected static string $resource = CteSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cte-list')
                ->label('Voltar para lista')
                ->color('gray')
                ->url(fn (): string => CteSaidaResource::getUrl('index')),

            ActionGroup::make([
                DownloadXmlAction::make(),
                DownloadPdfCteAction::make(),
            ])
                ->button()
                ->label('Download'),
        ];
    }
}
