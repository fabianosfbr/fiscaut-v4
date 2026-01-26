<?php

namespace App\Filament\Resources\Cnaes\Pages;

use App\Filament\Resources\Cnaes\CnaeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCnaes extends ListRecords
{
    protected static string $resource = CnaeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adcionar Novo'),
        ];
    }
}
