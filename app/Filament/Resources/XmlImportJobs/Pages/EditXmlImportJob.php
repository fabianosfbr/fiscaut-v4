<?php

namespace App\Filament\Resources\XmlImportJobs\Pages;

use App\Filament\Resources\XmlImportJobs\XmlImportJobResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditXmlImportJob extends EditRecord
{
    protected static string $resource = XmlImportJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
