<?php

namespace App\Filament\Resources\CteSaidas\Pages;

use App\Filament\Resources\CteSaidas\CteSaidaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCteSaidas extends ListRecords
{
    protected static string $resource = CteSaidaResource::class;

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
