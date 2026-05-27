<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\LogSefazNfeEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class AutenticidadeNfceJob implements ShouldQueue
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

        LogSefazNfeEvent::query()
            ->where('tp_evento', 110111)
            ->orWhere('tp_evento', 110112)
            ->where('dh_evento', '>=', $endDate)
            ->where('is_verificado_sefaz', false)
            ->where('issuer_id', $this->issuer->id)
            ->distinct()
            ->get()
            ->each(fn (LogSefazNfeEvent $evento) => AutenticidadeNfceCheckJob::dispatch($evento)->onQueue('low'));
    }
}
