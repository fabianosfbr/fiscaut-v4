<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Pages;

use App\Filament\Condominio\Resources\IssuerDocuments\IssuerDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListIssuerDocuments extends ListRecords
{
    protected static string $resource = IssuerDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo')
                ->modalHeading('Adicionar Novo Documento')
                ->mutateDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    $data['issuer_id'] = currentIssuer()->id;
                    $data['tenant_id'] = currentIssuer()->tenant_id;

                    $data['extension'] = pathinfo($data['file_path'], PATHINFO_EXTENSION);
                    $data['original_name'] = basename($data['file_path']);

                    if (Storage::disk('local')->exists($data['file_path'])) {
                        $data['file_size'] = Storage::disk('local')->size($data['file_path']);
                        $data['mime_type'] = Storage::disk('local')->mimeType($data['file_path']);
                    }

                    return $data;
                }),
        ];
    }
}
