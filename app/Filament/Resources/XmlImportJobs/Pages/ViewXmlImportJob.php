<?php

namespace App\Filament\Resources\XmlImportJobs\Pages;

use App\Filament\Resources\XmlImportJobs\XmlImportJobResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewXmlImportJob extends ViewRecord
{
    protected static string $resource = XmlImportJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('voltar')
                ->label('Voltar')
                ->url(fn () => XmlImportJobResource::getUrl('index')),
        ];
    }
}
