<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\NotaFiscalConsumidor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;

class AutenticidadeNfceJob implements ShouldQueue
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
        $retentionDays = config('admin.schedule_antenticidate_days', 7);
        $endDate = Carbon::now()->subDays($retentionDays);

        $eventos = DB::table('log_sefaz_nfe_events')
            ->where('tp_evento', 110111)
            ->where('dh_evento', '>=', $endDate)
            ->where('is_verificado_sefaz', false)
            ->where('issuer_id', $this->issuer->id)
            ->distinct()->get();

        foreach ($eventos as $evento) {
            $nfe = NotaFiscalConsumidor::where('chave', $evento->chave)
                ->where('status_nota', 100)
                ->first();

            if (isset($nfe) and isset($nfe->xml)) {
                $stdCl = new Standardize(gzuncompress($nfe->xml));

                $arr = $stdCl->toArray();

                $result = searchValueInArray($arr, 'tpEvento');

                if ($result != '110111') {

                    $xml = Complements::cancelRegister(gzuncompress($nfe->xml), $evento->xml);

                    $nfe->update(['status_nota' => 101, 'xml' => gzcompress($xml)]);

                    DB::table('log_sefaz_nfe_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);

                    Log::warning('Nfe cancelada:'.$nfe->chave);
                }

                if ($result == '110111') {

                    DB::table('log_sefaz_nfe_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);
                }
            }
        }
    }
}
