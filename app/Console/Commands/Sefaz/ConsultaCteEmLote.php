<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\SefazCteDownloadAndProcessBatchJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class ConsultaCteEmLote extends Command
{
    protected $signature = 'app:sync-cte-sefaz';

    protected $description = 'Sincroniza CTes com a SEFAZ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $issuer = Issuer::find(11);

        SefazCteDownloadAndProcessBatchJob::dispatch($issuer);
    }
}
