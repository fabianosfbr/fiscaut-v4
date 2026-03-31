<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewIssuerControl extends ViewRecord
{
    protected static string $resource = IssuerControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::getResource()::getUrl()),
        ];
    }
}
