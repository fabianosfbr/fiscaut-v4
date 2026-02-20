<?php

namespace App\Jobs\Sefaz;

use App\Models\ConhecimentoTransporteEletronico;
use App\Models\Issuer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NFePHP\CTe\Common\Standardize;
use NFePHP\CTe\Complements;

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

        $eventos = DB::table('log_sefaz_cte_events')
            ->where('tp_evento', 110111)
            ->where('dh_evento', '>=', $endDate)
            ->where('is_verificado_sefaz', false)
            ->where('issuer_id', $this->issuer->id)
            ->distinct()->get();

        foreach ($eventos as $evento) {
            $cte = ConhecimentoTransporteEletronico::where('chave', $evento->chave)->where('status_cte', 100)->first();

            if (isset($cte)) {
                $stdCl = new Standardize(gzuncompress($cte->xml));

                $arr = $stdCl->toArray();

                $result = searchValueInArray($arr, 'tpEvento');

                if ($result != '110111') {

                    $xml = Complements::cancelRegister(gzuncompress($cte->xml), $evento->xml);

                    $cte->update(['status_cte' => 101, 'xml' => gzcompress($xml)]);

                    DB::table('log_sefaz_cte_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);

                    Log::warning('CTe cancelada:'.$cte->chave);
                }

                if ($result == '110111') {

                    DB::table('log_sefaz_cte_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);
                }
            }
        }
    }
}
