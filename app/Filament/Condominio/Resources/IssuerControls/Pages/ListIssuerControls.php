<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListIssuerControls extends ListRecords
{
    protected static string $resource = IssuerControlResource::class;

    protected function getHeaderActions(): array
    {
        return [            
            Action::make('manage')
                ->label('Gerenciar Controles')
                ->url(fn () => ManageIssuerControls::getUrl()),
        ];
    }
}
