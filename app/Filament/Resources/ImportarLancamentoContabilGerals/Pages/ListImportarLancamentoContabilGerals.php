<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals\Pages;

use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Actions\ImportarLancamentoContabilGeralAction;
use App\Filament\Actions\GerarArquivoTxtLancamentoContabilGeral;
use App\Filament\Resources\ImportarLancamentoContabilGerals\Widgets\ImportLancamentoOverview;
use App\Filament\Resources\ImportarLancamentoContabilGerals\ImportarLancamentoContabilGeralResource;

class ListImportarLancamentoContabilGerals extends ListRecords
{
    protected static string $resource = ImportarLancamentoContabilGeralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportarLancamentoContabilGeralAction::make(),
            GerarArquivoTxtLancamentoContabilGeral::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ImportLancamentoOverview::class,
        ];
    }

        public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
