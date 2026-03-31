<?php

namespace App\Http\Controllers;

use App\Models\IssuerAssembleia;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IssuerAssembleiaDocumentController extends Controller
{
    public function show(IssuerAssembleia $record): StreamedResponse
    {
        $currentIssuer = currentIssuer();

        if (! $currentIssuer || $record->issuer_id !== $currentIssuer->id) {
            abort(403, 'Você não tem permissão para acessar este documento.');
        }

        $documentPath = $record->document_path;

        if (! $documentPath || ! Storage::disk('local')->exists($documentPath)) {
            abort(404);
        }

        return Storage::disk('local')->response($documentPath, basename($documentPath), [
            'Content-Disposition' => 'attachment',
        ]);
    }
}
