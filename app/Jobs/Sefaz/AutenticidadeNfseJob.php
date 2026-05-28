<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\LogSefazNfseEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class AutenticidadeNfseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $endDate = Carbon::now()->subDays(30);

        LogSefazNfseEvent::query()
            ->where('x_desc', 'like', '%cancelamento%')
            ->where('issuer_id', $this->issuer->id)
            ->where('dh_evento', '>=', $endDate)
            ->distinct()
            ->chunkById(100, function ($eventos) {
                foreach ($eventos as $evento) {
                    AutenticidadeNfseCheckJob::dispatch($evento)->onQueue('low');
                }
            });
    }
}
