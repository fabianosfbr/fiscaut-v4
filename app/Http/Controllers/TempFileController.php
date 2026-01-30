<?php

namespace App\Http\Controllers;

use App\Models\TempFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TempFileController extends Controller
{
    public function download(Request $request, TempFile $tempFile): StreamedResponse
    {
        abort_if($request->user()?->id !== $tempFile->user_id, 404);
        abort_if($tempFile->isExpired(), 404);

        $disk = $tempFile->disk;
        $path = $tempFile->file_path;

        abort_if(! is_string($disk) || $disk === '', 404);
        abort_if(! is_string($path) || $path === '', 404);
        abort_if(Str::startsWith($path, ['/', '\\']), 404);
        abort_if(Str::contains($path, ['..', "\0"]), 404);
        abort_if(! Str::startsWith($path, 'temp-downloads/'), 404);

        try {
            $storage = Storage::disk($disk);
        } catch (Throwable) {
            abort(404);
        }

        abort_if(! $storage->exists($path), 404);

        $originalName = is_string($tempFile->original_name) && $tempFile->original_name !== ''
            ? $tempFile->original_name
            : basename($path);

        return $storage->download(
            path: $path,
            name: $originalName,
            headers: [
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
            ],
        );
    }
}
