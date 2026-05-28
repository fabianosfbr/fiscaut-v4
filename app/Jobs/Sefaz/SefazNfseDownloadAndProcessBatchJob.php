<?php

namespace App\Jobs\Sefaz;

use App\Enums\XmlImportJobType;
use App\Models\Issuer;
use App\Models\XmlImportJob;
use App\Services\Sefaz\SefazNfseDownloadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SefazNfseDownloadAndProcessBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    public function __construct(
        protected Issuer $issuer,
        protected ?string $ultNsu = null
    ) {}

    public function handle(): void
    {
        try {
            $service = new SefazNfseDownloadService($this->issuer);
            $result = $service->downloadNfseInBatch();

            $totalFiles = $result['total_documentos'];

            // If no documents found, just log and return
            if (empty($result['documentos'])) {
                Log::info('Nenhum documento NFSe encontrado para processamento', [
                    'issuer_id' => $this->issuer->id,
                ]);

                return;
            }

            $importJob = $this->createXmlImportJob($totalFiles);
            $importJob->updateQuietly([
                'status' => XmlImportJob::STATUS_PROCESSING,
                'total_files' => $totalFiles,
            ]);

            // Dispatch one job per document directly
            foreach ($result['documentos'] as $documento) {
                SefazNfseProcessDocumentJob::dispatch($documento, $this->issuer, $importJob)
                    ->onQueue('sefaz');
            }
        } catch (\Throwable $e) {
            Log::error('Erro no download e processamento de NFSe da SEFAZ', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function createXmlImportJob(int $totalFiles): XmlImportJob
    {
        return XmlImportJob::createQuietly([
            'tenant_id' => $this->issuer->tenant_id,
            'issuer_id' => $this->issuer->id,
            'owner_id' => $this->issuer->id,
            'owner_type' => $this->issuer::class,
            'import_type' => XmlImportJobType::SEFAZ_NFSE,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => $totalFiles,
            'errors' => [],
        ]);
    }
}
