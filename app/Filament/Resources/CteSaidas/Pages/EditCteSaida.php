<?php

namespace App\Filament\Resources\CteSaidas\Pages;

use App\Filament\Resources\CteSaidas\CteSaidaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCteSaida extends EditRecord
{
    protected static string $resource = CteSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
