<?php

namespace App\Filament\Resources\CodigosServicos\Pages;

use App\Filament\Resources\CodigosServicos\CodigoServicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCodigosServicos extends ListRecords
{
    protected static string $resource = CodigoServicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
