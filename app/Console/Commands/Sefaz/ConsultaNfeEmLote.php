<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\SefazNfeDownloadAndProcessBatchJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class ConsultaNfeEmLote extends Command
{
    protected $signature = 'app:sync-nfe-sefaz 
                                {--issuer= : ID do emitente para download específico}';

    protected $description = 'Sincroniza NFes com a SEFAZ';

    public function handle()
    {
        $this->info('Iniciando sincronização de NFes com a SEFAZ');

        $issuerId = $this->option('issuer');

        $issuers = Issuer::where('validade_certificado', '>', now())
            ->where('is_enabled', true)
            ->where('nfe_servico', true)
            ->when($issuerId !== null, fn($q) => $q->where('id', $issuerId))
            ->get();


        foreach ($issuers as $issuer) {
            // Dispatch the batch job
            SefazNfeDownloadAndProcessBatchJob::dispatch($issuer);
        }

        $this->info('Sincronização de NFes com a SEFAZ concluída');

        return self::SUCCESS;
    }
}
