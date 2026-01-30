<?php

namespace App\Filament\Resources\NfeSaidas\Pages;

use App\Filament\Resources\NfeSaidas\NfeSaidaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNfeSaidas extends ListRecords
{
    protected static string $resource = NfeSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
