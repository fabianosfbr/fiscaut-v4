<?php

namespace App\Filament\Resources\UploadFileManagers\Pages;

use App\Filament\Resources\UploadFileManagers\UploadFileManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUploadFileManagers extends ListRecords
{
    protected static string $resource = UploadFileManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Enviar arquivo'),
        ];
    }
}
