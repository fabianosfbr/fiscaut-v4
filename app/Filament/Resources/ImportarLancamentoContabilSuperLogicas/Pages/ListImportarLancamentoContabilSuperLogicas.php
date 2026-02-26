<?php

namespace App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Pages;

use App\Filament\Actions\ImportarLancamentoContabilSuperLogicaAction;
use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\ImportarLancamentoContabilSuperLogicaResource;
use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Widgets\ImportLancamentoSuperLogicaOverview;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListImportarLancamentoContabilSuperLogicas extends ListRecords
{
    protected static string $resource = ImportarLancamentoContabilSuperLogicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportarLancamentoContabilSuperLogicaAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ImportLancamentoSuperLogicaOverview::class,
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
