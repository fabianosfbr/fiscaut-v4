<?php

namespace App\Filament\Resources\Cnaes\Pages;

use App\Filament\Resources\Cnaes\CnaeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCnae extends CreateRecord
{
    protected static string $resource = CnaeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
