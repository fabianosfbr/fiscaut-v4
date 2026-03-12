<?php

namespace App\Filament\Condominio\Resources\IssuerGroupControls\Pages;

use App\Filament\Condominio\Resources\IssuerGroupControls\IssuerGroupControlResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuerGroupControls extends ListRecords
{
    protected static string $resource = IssuerGroupControlResource::class;


    protected static ?string $title = 'Grupos de Controle';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label("Adicionar Novo"),
        ];
    }

    
}
