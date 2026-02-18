<?php

namespace App\Http\Controllers;

use App\Models\SecureDownload;
use App\Models\SecureDownloadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureDownloadController extends Controller
{
    /**
     * Handle the secure download via GET (from notifications).
     */
    public function download(string $uuid)
    {
        $secureDownload = SecureDownload::where('id', $uuid)->firstOrFail();

        return $this->processDownload($secureDownload);
    }

    /**
     * Handle the secure download via POST as requested.
     */
    public function downloadPost(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid|exists:secure_downloads,uuid',
            'doc_type' => 'nullable|string', // XML/PDF
            'job_class' => 'nullable|string',
        ]);

        $secureDownload = SecureDownload::where('uuid', $validated['uuid'])->firstOrFail();

        // Optional additional validation based on doc_type or job_class if provided
        if (isset($validated['job_class']) && $secureDownload->job_class !== $validated['job_class']) {
            abort(403, 'Job class mismatch.');
        }

        return $this->processDownload($secureDownload);
    }

    /**
     * Common logic for processing the download.
     */
    private function processDownload(SecureDownload $secureDownload)
    {
        // 1. Authentication & Authorization
        if (!Auth::check()) {
            abort(401, 'Usuário não autenticado.');
        }

        $user = Auth::user();

        // 4. Verificação de Permissões (Propriedade ou Roles)
        // Check if the user is the owner OR has admin role (if exists)
        if ($secureDownload->user_id !== $user->id && !$this->userIsAdmin($user)) {
            abort(403, 'Você não tem permissão para acessar este arquivo.');
        }

        // Check expiration
        if ($secureDownload->isExpired()) {
            abort(410, 'Este link de download expirou.');
        }

        // 3. Localização do Arquivo
        if (!Storage::disk('local')->exists($secureDownload->file_path)) {
            abort(404, 'Arquivo não encontrado no storage.');
        }


        // 5. Processo de Download (Streaming)
        return Storage::disk('local')->download(
            $secureDownload->file_path,
            $secureDownload->file_name,
            [
                'Content-Type' => $secureDownload->mime_type,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    /**
     * Helper to check if user is admin.
     * Adjust this logic based on your system's role implementation.
     */
    private function userIsAdmin($user): bool
    {
        // Example: if using a role relation
        // return $user->role && $user->role->name === 'admin';
        
        // Or if using a simple boolean field
        // return (bool) $user->is_admin;

        // Based on User model, we have $user->role()
        return $user->role && in_array($user->role->name, ['admin', 'super-admin']);
    }
}
