<?php

namespace App\Console\Commands\Superlogica;

use App\Jobs\NotificaJuridicoSuperLogicaJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class NotificaJuridico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notifica-cobranca';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica as unidades sobre inadimplências';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $issuers = Issuer::with('tenant')
            ->where('is_enabled', true)
            ->whereNotNull('superlogica_condominio_id')
            ->get();

        foreach ($issuers as $issuer) {
            NotificaJuridicoSuperLogicaJob::dispatch($issuer);
        }

    }
}
