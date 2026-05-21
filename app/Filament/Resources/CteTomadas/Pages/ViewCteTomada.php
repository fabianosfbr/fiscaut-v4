<?php

namespace App\Filament\Resources\CteTomadas\Pages;

use App\Filament\Actions\DownloadPdfCteAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Resources\CteTomadas\CteTomadaResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;

class ViewCteTomada extends ViewRecord
{
    protected static string $resource = CteTomadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cte-list')
                ->label('Voltar para lista')
                ->color('gray')
                ->url(fn (): string => CteTomadaResource::getUrl('index')),
            Action::make('manifestar-cte')
                ->label('Manifestar CTE')
                ->icon('heroicon-o-book-open')
                ->button()
                ->color('primary')
                ->action(function () {
                    dd('fiscaut');
                }),
            ActionGroup::make([
                DownloadXmlAction::make(),
                DownloadPdfCteAction::make(),
            ])
                ->button()
                ->label('Download'),
        ];
    }
}
