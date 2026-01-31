<?php

namespace App\Filament\Resources\CteTomadas\Pages;

use App\Filament\Resources\CteTomadas\CteTomadaResource;
use Filament\Actions\Action;
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
        ];
    }
}
