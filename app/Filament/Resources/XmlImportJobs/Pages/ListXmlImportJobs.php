<?php

namespace App\Filament\Resources\XmlImportJobs\Pages;

use App\Filament\Resources\XmlImportJobs\XmlImportJobResource;
use Filament\Resources\Pages\ListRecords;

class ListXmlImportJobs extends ListRecords
{
    protected static string $resource = XmlImportJobResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Histórico de Importações';
    }
}
