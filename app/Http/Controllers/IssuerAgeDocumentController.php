<?php

namespace App\Http\Controllers;

use App\Models\IssuerAge;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IssuerAgeDocumentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function show(IssuerAge $record): StreamedResponse
    {
        if (! $record->document_path || ! Storage::disk('local')->exists($record->document_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($record->document_path, null, [
            'Content-Disposition' => 'inline',
        ]);
    }
}
