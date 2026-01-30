<?php

namespace App\Filament\Resources\LogSefazCteContents\Pages;

use App\Filament\Resources\LogSefazCteContents\LogSefazCteContentResource;
use Filament\Resources\Pages\ListRecords;

class ListLogSefazCteContents extends ListRecords
{
    protected static string $resource = LogSefazCteContentResource::class;

    protected static ?string $title = 'Logs de NSU - CTe';

    protected function getHeaderWidgets(): array
    {
        return LogSefazCteContentResource::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
