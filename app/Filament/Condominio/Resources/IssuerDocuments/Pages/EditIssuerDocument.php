<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Pages;

use App\Filament\Condominio\Resources\IssuerDocuments\IssuerDocumentResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditIssuerDocument extends EditRecord
{
    protected static string $resource = IssuerDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        $data['validate_at'] = Carbon::parse($data['validate_at'])->format('Y-m-d');
        return $data;
    }
}
