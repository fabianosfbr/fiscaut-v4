<?php

namespace App\Filament\Resources\Cfops\Pages;

use App\Filament\Resources\Cfops\CfopResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCfops extends ListRecords
{
    protected static string $resource = CfopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
