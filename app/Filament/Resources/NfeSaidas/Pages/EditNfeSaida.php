<?php

namespace App\Filament\Resources\NfeSaidas\Pages;

use App\Filament\Resources\NfeSaidas\NfeSaidaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNfeSaida extends EditRecord
{
    protected static string $resource = NfeSaidaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
