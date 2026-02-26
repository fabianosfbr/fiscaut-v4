<?php

namespace App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Pages;

use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\ImportarLancamentoContabilSuperLogicaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditImportarLancamentoContabilSuperLogica extends EditRecord
{
    protected static string $resource = ImportarLancamentoContabilSuperLogicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
