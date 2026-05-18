<?php

namespace App\Http\Controllers;

use App\Services\SuperlogicaConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CondosrContaPagarArquivoController extends Controller
{
    public function download(string $id, string $hash)
    {
        try {
            $issuer = currentIssuer();

            if (!$issuer) {
                abort(403, 'Emitente não encontrado.');
            }

            $service = new SuperlogicaConnectionService($issuer->tenant);

            $fileContent = $service
                ->documento()
                ->download([
                    'id' => $id,
                    'hash' => $hash,
                ]);

            if (empty($fileContent)) {
                abort(404, 'Arquivo não encontrado na Superlógica.');
            }

            $cleanFilename = $this->sanitizeFilename($hash . '.pdf');

            return response($fileContent)->withHeaders([
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $cleanFilename . '"',
            ]);

            // return response($fileContent, 200, [
            //     'Content-Type' => 'application/pdf',
            //     'Content-Disposition' => 'attachment; filename="' . $cleanFilename . '"',
            // ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao baixar arquivo da Superlógica', [
                'id' => $id,
                'hash' => $hash,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Erro ao processar o download do arquivo.');
        }
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_{2,}/', '_', $filename);
        return trim($filename, '_');
    }
}
