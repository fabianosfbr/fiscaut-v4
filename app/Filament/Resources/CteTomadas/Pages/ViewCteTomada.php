<?php

namespace App\Filament\Resources\CteTomadas\Pages;

use App\Filament\Resources\CteTomadas\CteTomadaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCteTomada extends ViewRecord
{
    protected static string $resource = CteTomadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
