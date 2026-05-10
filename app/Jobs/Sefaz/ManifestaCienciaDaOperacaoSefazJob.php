<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Services\Sefaz\SefazNfeDownloadService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ManifestaCienciaDaOperacaoSefazJob implements ShouldQueue
{
    use Queueable;

    public $failOnTimeout = false;

    public $timeout = 120000;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Issuer $issuer
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = new SefazNfeDownloadService($this->issuer);

        $service->manifestaCienciaDaOperacao();

        Log::info('Manifesta Ciência da Operação Automática - Empresa:  ' . explode(':', $this->issuer->razao_social)[0] . ' em: ' . date('d-m-y H:i:s'));
    }
}
