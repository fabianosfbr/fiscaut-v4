<?php

namespace App\Filament\Resources\LogSefazNfeContents\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\LogSefazNfeContents\Widgets\MaxNsuOverview;
use App\Filament\Resources\LogSefazNfeContents\Widgets\MinNsuOverview;
use App\Filament\Resources\LogSefazNfeContents\LogSefazNfeContentResource;

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
