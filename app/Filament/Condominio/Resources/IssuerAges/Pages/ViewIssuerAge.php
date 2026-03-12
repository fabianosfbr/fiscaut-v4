<?php

namespace App\Filament\Condominio\Resources\IssuerAges\Pages;

use App\Filament\Condominio\Resources\IssuerAges\IssuerAgeResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewIssuerAge extends ViewRecord
{
    protected static string $resource = IssuerAgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list')
                ->label('Voltar')
                ->color('gray')
                ->url(fn (): string => IssuerAgeResource::getUrl('index')),

        ];
    }
}
