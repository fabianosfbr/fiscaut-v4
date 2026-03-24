<?php

namespace App\Filament\Condominio\Resources\ManutencaoHistoricos\Pages;

use App\Filament\Condominio\Resources\ManutencaoHistoricos\ManutencaoHistoricoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManutencaoHistorico extends EditRecord
{
    protected static string $resource = ManutencaoHistoricoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
