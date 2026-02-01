<?php

namespace App\Filament\Resources\CteEntradas\Pages;

use App\Filament\Resources\CteEntradas\CteEntradaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCteEntradas extends ListRecords
{
    protected static string $resource = CteEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return 'Conhecimentos de Transporte Eletrônicos';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
