<?php

namespace App\Filament\Resources\LogSefazNfeContents\Pages;

use App\Filament\Resources\LogSefazNfeContents\LogSefazNfeContentResource;
use Filament\Resources\Pages\EditRecord;

class EditLogSefazNfeContent extends EditRecord
{
    protected static string $resource = LogSefazNfeContentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
