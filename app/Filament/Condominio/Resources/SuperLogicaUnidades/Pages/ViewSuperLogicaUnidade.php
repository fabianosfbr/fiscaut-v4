<?php

namespace App\Filament\Condominio\Resources\SuperLogicaUnidades\Pages;

use App\Filament\Condominio\Resources\SuperLogicaUnidades\SuperLogicaUnidadeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Contracts\HasSchemas;

class ViewSuperLogicaUnidade extends ViewRecord implements HasSchemas
{
    protected static string $resource = SuperLogicaUnidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
