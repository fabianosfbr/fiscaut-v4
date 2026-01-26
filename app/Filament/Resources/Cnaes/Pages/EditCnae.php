<?php

namespace App\Filament\Resources\Cnaes\Pages;

use App\Filament\Resources\Cnaes\CnaeResource;
use Filament\Resources\Pages\EditRecord;

class EditCnae extends EditRecord
{
    protected static string $resource = CnaeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
