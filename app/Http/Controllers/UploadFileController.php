<?php

namespace App\Http\Controllers;

use App\Models\UploadFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UploadFileController extends Controller
{
    public function preview(int $id)
    {

        $file = UploadFile::whereId($id)
            ->where('issuer_id', currentIssuer()?->id)
            ->firstOrFail();

        $fileContent = Storage::get($file->path);
        $mimeType = Storage::mimeType($file->path);
        $fileName = $this->generateFileName($file, $mimeType);

        if ($this->isCompressedFile($mimeType)) {
            return $this->downloadCompressedFile($fileContent, $fileName, $mimeType);
        }

        return response($fileContent)->withHeaders([
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    private function generateFileName(UploadFile $file, string $mimeType): string
    {
        $extension = $this->getFileExtension($mimeType);
        $fileName = $file->id.'-'.$file->title.$extension;

        return str_replace('/', '-', $fileName);
    }

    private function getFileExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'application/zip' => '.zip',
            'application/x-rar' => '.rar',
            default => ''
        };
    }

    private function isCompressedFile(string $mimeType): bool
    {
        return in_array($mimeType, ['application/zip', 'application/x-rar']);
    }

    private function downloadCompressedFile(string $fileContent, string $fileName, string $mimeType): StreamedResponse
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName, [
            'Content-Description' => 'File Transfer',
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
