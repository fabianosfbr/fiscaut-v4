<?php

namespace App\Filament\Resources\NfeEntradas\Pages;

use App\Filament\Resources\NfeEntradas\NfeEntradaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNfeEntradas extends ListRecords
{
    protected static string $resource = NfeEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
