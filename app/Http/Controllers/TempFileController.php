<?php

namespace App\Http\Controllers;

use App\Models\TempFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TempFileController extends Controller
{
    public function download(TempFile $tempFile): StreamedResponse
    {
        abort_if($tempFile->user_id !== Auth::id(), 403);
        abort_if($tempFile->isExpired(), 404);

        if (Storage::disk($tempFile->disk)->exists($tempFile->file_path)) {
            return Storage::disk($tempFile->disk)
                ->download(
                    path: $tempFile->file_path,
                    name: $tempFile->original_name
                );
        }

        abort(404);
    }
}
