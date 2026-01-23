<?php

namespace App\Filament\Resources\Issuers\Pages;

use App\Filament\Resources\Issuers\IssuerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIssuers extends ListRecords
{
    protected static string $resource = IssuerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Nova'),
        ];
    }
}
