<?php

namespace App\Http\Controllers;

use App\Models\IssuerAge;
use App\Models\IssuerControl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IssuerRagDocumentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function show(IssuerControl $record): StreamedResponse
    {
        $documentPath = $record->value['document_path'] ?? $record->document_path ?? null;

        if (!$documentPath || !Storage::disk('local')->exists($documentPath)) {
            abort(404);
        }


        return Storage::disk('local')->response($documentPath, null, [
            'Content-Disposition' => 'inline',
        ]);
    }
}
