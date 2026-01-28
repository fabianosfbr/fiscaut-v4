<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;

use App\Models\XmlImportJob;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Enums\XmlImportJobType;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Sefaz\SefazCteDownloadService;


class SefazCteDownloadAndProcessBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Issuer $issuer,
        protected ?string $ultNsu = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Initialize the download service
            $service = new SefazCteDownloadService($this->issuer);

            // Download documents in batch
            $result = $service->downloadCteInBatch($this->ultNsu);

            $importJob = $this->createXmlImportJob($result['total_documentos']);

            // If documents were found, process them in a batch
            if (! empty($result['documentos'])) {
                SefazCteDownloadBatchJob::dispatch(
                    $importJob,
                    $result['documentos'],
                    $this->issuer,
                    $result['ultimo_nsu']
                );
            } else {
                Log::info('Nenhum documento encontrado para processamento', [
                    'issuer_id' => $this->issuer->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro no download e processamento de documentos da SEFAZ', [
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
