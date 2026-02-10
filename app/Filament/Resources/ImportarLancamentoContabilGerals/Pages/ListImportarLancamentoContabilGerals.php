<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals\Pages;

use App\Filament\Actions\GerarArquivoTxtLancamentoContabilGeral;
use App\Filament\Actions\ImportarLancamentoContabilGeralAction;
use App\Filament\Resources\ImportarLancamentoContabilGerals\ImportarLancamentoContabilGeralResource;
use App\Filament\Resources\ImportarLancamentoContabilGerals\Widgets\ImportLancamentoOverview;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

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
