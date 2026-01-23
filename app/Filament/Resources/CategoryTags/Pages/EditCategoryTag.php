<?php

namespace App\Filament\Resources\CategoryTags\Pages;

use App\Filament\Resources\CategoryTags\CategoryTagResource;
use Filament\Resources\Pages\EditRecord;

class EditCategoryTag extends EditRecord
{
    protected static string $resource = CategoryTagResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
