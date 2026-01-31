<?php

namespace App\Filament\Resources\CteTomadas\Pages;

use App\Filament\Resources\CteTomadas\CteTomadaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCteTomada extends EditRecord
{
    protected static string $resource = CteTomadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
