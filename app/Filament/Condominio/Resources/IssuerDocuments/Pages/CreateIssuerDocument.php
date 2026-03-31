<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Pages;

use App\Filament\Condominio\Resources\IssuerDocuments\IssuerDocumentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateIssuerDocument extends CreateRecord
{
    protected static string $resource = IssuerDocumentResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        $data['issuer_id'] = currentIssuer()->id;
        $data['tenant_id'] = currentIssuer()->tenant_id;

        $data['extension'] = pathinfo($data['file_path'], PATHINFO_EXTENSION);
        $data['original_name'] = basename($data['file_path']);

        if (Storage::disk('local')->exists($data['file_path'])) {
            $data['file_size'] = Storage::disk('local')->size($data['file_path']);
            $data['mime_type'] = Storage::disk('local')->mimeType($data['file_path']);
        }

        return $data;
    }
}
