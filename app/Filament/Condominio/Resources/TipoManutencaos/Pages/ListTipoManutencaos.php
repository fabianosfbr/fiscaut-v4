<?php

namespace App\Filament\Condominio\Resources\TipoManutencaos\Pages;

use App\Filament\Condominio\Resources\TipoManutencaos\TipoManutencaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoManutencaos extends ListRecords
{
    protected static string $resource = TipoManutencaoResource::class;

    protected static ?string $title = 'Tipos de Manutenção';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
