<?php

namespace App\Filament\Resources\HistoricoContabils\Pages;

use App\Filament\Resources\HistoricoContabils\HistoricoContabilResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHistoricoContabils extends ListRecords
{
    protected static string $resource = HistoricoContabilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Adicionar Novo'),
        ];
    }
}
