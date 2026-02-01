<?php

namespace App\Filament\Resources\CteEntradas\Pages;

use App\Filament\Resources\CteEntradas\CteEntradaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCteEntrada extends EditRecord
{
    protected static string $resource = CteEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
