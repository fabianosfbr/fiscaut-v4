<?php

namespace App\Jobs\Sefaz;

use App\Models\LogSefazNfeEvent;
use App\Models\NotaFiscalConsumidor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;

class AutenticidadeNfceCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected LogSefazNfeEvent $evento
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $nfe = NotaFiscalConsumidor::where('chave', $this->evento->chave)
            ->where('status_nota', 100)
            ->first();

        if (! isset($nfe) || ! isset($nfe->xml)) {
            return;
        }

        $stdCl = new Standardize(gzuncompress($nfe->xml));

        $arr = $stdCl->toArray();

        $result = searchValueInArray($arr, 'tpEvento');

        if ($result == '110111' || $result == '110112') {
            $xml = Complements::cancelRegister(gzuncompress($nfe->xml), $this->evento->xml);

            $nfe->update(['status_nota' => 101, 'xml' => gzcompress($xml)]);

            DB::table('log_sefaz_nfe_events')
                ->where('id', $this->evento->id)
                ->update(['is_verificado_sefaz' => true]);

            Log::warning('Nfce cancelada:'.$nfe->chave);
        }
    }
}
