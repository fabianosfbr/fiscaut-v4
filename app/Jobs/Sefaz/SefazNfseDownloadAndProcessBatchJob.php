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
            $result = $service->downloadNfseInBatch($this->ultNsu);

            $importJob = $this->createXmlImportJob($result['total_documentos']);

            if (! empty($result['documentos'])) {
                SefazNfseDownloadBatchJob::dispatch(
                    $importJob,
                    $result['documentos'],
                    $this->issuer,
                    $result['ultimo_nsu']
                );

                return;
            }

            $importJob->updateQuietly([
                'status' => XmlImportJob::STATUS_COMPLETED,
                'total_files' => 0,
            ]);
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
            'import_type' => XmlImportJobType::SYSTEM,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => $totalFiles,
            'errors' => [],
        ]);
    }
}

