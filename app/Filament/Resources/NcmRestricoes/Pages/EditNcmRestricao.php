<?php

namespace App\Filament\Resources\NcmRestricoes\Pages;

use App\Filament\Resources\NcmRestricoes\NcmRestricaoResource;
use Filament\Resources\Pages\EditRecord;

class EditNcmRestricao extends EditRecord
{
    protected static string $resource = NcmRestricaoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
