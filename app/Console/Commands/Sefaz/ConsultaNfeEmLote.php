<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\SefazNfeDownloadAndProcessBatchJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class ConsultaNfeEmLote extends Command
{
    protected $signature = 'app:sync-nfe-sefaz';

    protected $description = 'Sincroniza NFes com a SEFAZ';

    public function handle()
    {
        $this->info('Iniciando sincronização de NFes com a SEFAZ');

        $issuer = Issuer::find(11);

        if (! $issuer) {
            $this->error('Issuer 11 não encontrado. Nenhum job foi disparado.');

            return self::FAILURE;
        }

        SefazNfeDownloadAndProcessBatchJob::dispatch($issuer);

        // $issuers = Issuer::where('validade_certificado', '>', now())
        //     ->where('is_enabled', true)
        //     ->where('nfe_servico', true)
        //     ->get();

        // foreach ($issuers as $issuer) {
        //     // Dispatch the batch job
        //     SefazNfeDownloadAndProcessBatchJob::dispatch($issuer);
        // }

        $this->info('Sincronização de NFes com a SEFAZ concluída');

        return self::SUCCESS;
    }
}
