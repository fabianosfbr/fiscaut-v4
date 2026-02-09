<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals\Pages;

use App\Filament\Resources\ImportarLancamentoContabilGerals\ImportarLancamentoContabilGeralResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditImportarLancamentoContabilGeral extends EditRecord
{
    protected static string $resource = ImportarLancamentoContabilGeralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
