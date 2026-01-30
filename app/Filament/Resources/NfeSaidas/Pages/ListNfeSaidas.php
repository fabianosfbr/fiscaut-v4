<?php

namespace App\Filament\Resources\NfeSaidas\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\NfeSaidas\NfeSaidaResource;

class ListNfeSaidas extends ListRecords
{
    protected static string $resource = NfeSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return 'Notas Fiscais Eletrônicas';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
