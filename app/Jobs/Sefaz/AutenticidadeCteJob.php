<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\LogSefazCteEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class AutenticidadeCteJob implements ShouldQueue
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
        $endDate = Carbon::now()->subDays(30);

        LogSefazCteEvent::query()
            ->where('tp_evento', 110111)
            ->where('dh_evento', '>=', $endDate)
            ->where('is_verificado_sefaz', false)
            ->where('issuer_id', $this->issuer->id)
            ->distinct()
            ->get()
            ->each(fn (LogSefazCteEvent $evento) => AutenticidadeCteCheckJob::dispatch($evento)->onQueue('low'));
    }
}
