<?php

namespace App\Filament\Resources\LogSefazCteContents\Pages;

use App\Filament\Resources\LogSefazCteContents\LogSefazCteContentResource;
use Filament\Resources\Pages\EditRecord;

class EditLogSefazCteContent extends EditRecord
{
    protected static string $resource = LogSefazCteContentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
