<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\SefazCteDownloadAndProcessBatchJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class ConsultaCteEmLote extends Command
{
    protected $signature = 'app:sync-cte-sefaz
                                {--issuer= : ID do emitente para download específico}';

    protected $description = 'Sincroniza CTes com a SEFAZ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $issuerId = $this->option('issuer');

        $issuers = Issuer::where('validade_certificado', '>', now())
            ->where('is_enabled', true)
            ->where('cte_servico', true)
            ->when($issuerId !== null, fn($q) => $q->where('id', $issuerId))
            ->get();

        foreach ($issuers as $issuer) {
            // Dispatch the batch job
            SefazCteDownloadAndProcessBatchJob::dispatch($issuer);
        }

        return self::SUCCESS;
    }
}
