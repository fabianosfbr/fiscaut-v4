<?php

namespace App\Filament\Resources\Cfops\Pages;

use App\Filament\Resources\Cfops\CfopResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCfop extends EditRecord
{
    protected static string $resource = CfopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
