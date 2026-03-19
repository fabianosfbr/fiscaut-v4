<?php

namespace App\Filament\Condominio\Resources\ManutencaoRecorrencias\Pages;

use App\Filament\Condominio\Resources\ManutencaoRecorrencias\ManutencaoRecorrenciaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListManutencaoRecorrencias extends ListRecords
{
    protected static string $resource = ManutencaoRecorrenciaResource::class;

    protected static ?string $title = 'Recorrências';
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }
}
