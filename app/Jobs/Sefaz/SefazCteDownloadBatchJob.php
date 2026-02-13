<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\XmlImportJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SefazCteDownloadBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Tamanho do lote para processamento
     *
     * @var int
     */
    protected $batchSize = 50;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected XmlImportJob $importJob,
        protected array $documentos,
        protected Issuer $issuer,
        protected ?string $ultNsu = null
    ) {
        $this->onQueue('sefaz');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Create individual jobs for each document
            $jobs = [];
            foreach ($this->documentos as $documento) {
                $jobs[] = new SefazCteProcessDocumentJob($documento, $this->issuer, $this->importJob);
            }

            $importJobId = $this->importJob->id;
            $totalFiles = count($jobs);

            // Create a batch of jobs for processing
            Bus::batch($jobs)
                ->name('Processamento de CTes SEFAZ - Issuer '.$this->issuer->id)
                ->allowFailures()
                ->then(function () use ($totalFiles, $importJobId) {
                    // All jobs completed successfully

                    $importJob = XmlImportJob::find($importJobId);
                    if ($importJob) {
                        $importJob->updateQuietly([
                            'status' => XmlImportJob::STATUS_COMPLETED,
                            'total_files' => $totalFiles,
                        ]);
                    }
                })
                ->catch(function (\Throwable $e) {
                    $mensagemErro = 'Erro no processamento em lote de CTes SEFAZ: '.$e->getMessage();
                    Log::error('Erro no processamento em lote de CTes SEFAZ: '.$mensagemErro);

                    // Here you could add logic to handle the failure, such as:
                    // - Notifying the user
                    // - Updating a status in the database
                    // - Scheduling a retry
                })
                ->finally(function () use ($totalFiles) {
                    // The batch has finished executing
                    Log::info('Processamento em lote de CTes SEFAZ concluído. Total de arquivos na consulta: '.$totalFiles);
                })
                ->dispatch();
        } catch (\Throwable $e) {
            Log::error('Falha no processamento em lote de CTes SEFAZ: '.$e->getMessage());
            throw $e;
        }
    }
}
