<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals\Pages;

use App\Filament\Resources\ImportarLancamentoContabilGerals\ImportarLancamentoContabilGeralResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListImportarLancamentoContabilGerals extends ListRecords
{
    protected static string $resource = ImportarLancamentoContabilGeralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
