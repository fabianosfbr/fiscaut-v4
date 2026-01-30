<?php

namespace App\Filament\Resources\LogSefazNfeContents\Pages;

use App\Filament\Resources\LogSefazNfeContents\LogSefazNfeContentResource;
use Filament\Resources\Pages\ListRecords;

class ListLogSefazNfeContents extends ListRecords
{
    protected static string $resource = LogSefazNfeContentResource::class;

    protected static ?string $title = 'Logs de NSU - NFe';

    protected function getHeaderWidgets(): array
    {
        return LogSefazNfeContentResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
