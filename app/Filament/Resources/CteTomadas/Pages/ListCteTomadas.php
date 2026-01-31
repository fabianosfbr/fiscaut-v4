<?php

namespace App\Filament\Resources\CteTomadas\Pages;

use App\Filament\Resources\CteTomadas\CteTomadaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCteTomadas extends ListRecords
{
    protected static string $resource = CteTomadaResource::class;

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
