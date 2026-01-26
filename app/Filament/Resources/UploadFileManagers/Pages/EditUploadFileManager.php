<?php

namespace App\Filament\Resources\UploadFileManagers\Pages;

use App\Filament\Resources\UploadFileManagers\UploadFileManagerResource;
use Filament\Resources\Pages\EditRecord;

class EditUploadFileManager extends EditRecord
{
    protected static string $resource = UploadFileManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
