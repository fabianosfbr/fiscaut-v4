<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Services\Sefaz\SefazNfseDownloadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DownloadNfseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60; // 60 seconds

    protected Issuer $issuer;

    protected ?string $nsu;

    /**
     * Create a new job instance.
     */
    public function __construct(Issuer $issuer, ?string $nsu = null)
    {
        $this->onQueue('sefaz');
        $this->issuer = $issuer;
        $this->nsu = $nsu;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('Iniciando job de download de NFSE', [
                'issuer_id' => $this->issuer->id,
                'nsu' => $this->nsu,
            ]);

            $service = new SefazNfseDownloadService($this->issuer);

            $result = $service->downloadNfseInBatch($this->nsu);

            Log::info('Job de download de NFSE concluído com sucesso', [
                'issuer_id' => $this->issuer->id,
                'issuer_nome' => $this->issuer->razao_social,
                'total_documentos' => $result['total_documentos'],
                'ultimo_nsu' => $result['ultimo_nsu'],
                'nsu_inicial' => $result['nsu_inicial'],
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no job de download de NFSE', [
                'issuer_id' => $this->issuer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Lança a exceção para que o job seja reconhecido como falho
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Job de download de NFSE falhou após '.$this->tries.' tentativas', [
            'issuer_id' => $this->issuer->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
