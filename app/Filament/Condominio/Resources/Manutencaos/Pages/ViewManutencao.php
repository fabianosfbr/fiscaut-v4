<?php

namespace App\Filament\Condominio\Resources\Manutencaos\Pages;

use App\Filament\Condominio\Resources\Manutencaos\ManutencaoResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewManutencao extends ViewRecord
{
    protected static string $resource = ManutencaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('back')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::getResource()::getUrl()),
        ];
    }
}
