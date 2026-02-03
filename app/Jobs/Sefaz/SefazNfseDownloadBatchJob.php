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

class SefazNfseDownloadBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    public function __construct(
        protected XmlImportJob $importJob,
        protected array $documentos,
        protected Issuer $issuer,
        protected ?string $ultNsu = null
    ) {}

    public function handle(): void
    {
        try {
            $jobs = [];
            foreach ($this->documentos as $documento) {
                $jobs[] = new SefazNfseProcessDocumentJob($documento, $this->issuer, $this->importJob);
            }

            $importJobId = $this->importJob->id;
            $totalFiles = count($jobs);

            Bus::batch($jobs)
                ->name('Processamento de NFSe SEFAZ - Issuer '.$this->issuer->id)
                ->allowFailures()
                ->then(function () use ($totalFiles, $importJobId) {
                    $importJob = XmlImportJob::find($importJobId);
                    if ($importJob) {
                        $importJob->updateQuietly([
                            'status' => XmlImportJob::STATUS_COMPLETED,
                            'total_files' => $totalFiles,
                        ]);
                    }
                })
                ->catch(function (\Throwable $e) {
                    $mensagemErro = 'Erro no processamento em lote de documentos NFSe SEFAZ: '.$e->getMessage();
                    Log::error('Erro no processamento em lote de NFSe SEFAZ: '.$mensagemErro);
                })
                ->finally(function () use ($totalFiles) {
                    Log::info('Processamento em lote de NFSe SEFAZ concluído. Total de arquivos na consulta: '.$totalFiles);
                })
                ->dispatch();
        } catch (\Throwable $e) {
            Log::error('Falha no processamento em lote de NFSe SEFAZ: '.$e->getMessage());
            throw $e;
        }
    }
}
