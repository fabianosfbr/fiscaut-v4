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
        $endDate = Carbon::now()->subDays(40);

        LogSefazNfseEvent::query()
            ->where('x_desc', 'like', '%cancelamento%')
            ->where('issuer_id', $this->issuer->id)
            ->where('dh_evento', '>=', $endDate)
            ->distinct()
            ->get()
            ->each(fn (LogSefazNfseEvent $evento) => AutenticidadeNfseCheckJob::dispatch($evento)->onQueue('low'));
    }
}
