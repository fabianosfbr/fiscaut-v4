<?php

namespace App\Console\Commands\Sefaz;

use App\Models\Issuer;
use Illuminate\Console\Command;
use App\Jobs\Sefaz\SefazCteDownloadAndProcessBatchJob;

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
