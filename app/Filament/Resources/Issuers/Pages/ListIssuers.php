<?php

namespace App\Filament\Resources\Issuers\Pages;

use App\Filament\Actions\ImportIssuersAction;
use App\Filament\Resources\Issuers\IssuerResource;
use Filament\Actions\ActionGroup;
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
            ActionGroup::make([
                ImportIssuersAction::make(),
            ]),


        ];
    }
}
