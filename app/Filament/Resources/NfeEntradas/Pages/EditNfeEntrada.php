<?php

namespace App\Filament\Resources\NfeEntradas\Pages;

use App\Filament\Resources\NfeEntradas\NfeEntradaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNfeEntrada extends EditRecord
{
    protected static string $resource = NfeEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
