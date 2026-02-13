<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use NFePHP\NFe\Complements;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use NFePHP\NFe\Common\Standardize;
use Illuminate\Support\Facades\Log;
use App\Models\NotaFiscalEletronica;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AutenticidadeNfeJob implements ShouldQueue
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

        $eventos = DB::table('log_sefaz_nfe_events')
            ->where('tp_evento', 110111)
            ->where('dh_evento', '>=', $endDate)
            ->where('is_verificado_sefaz', false)
            ->where('issuer_id', $this->issuer->id)
            ->distinct()->get();

        foreach ($eventos as $evento) {
            $nfe = NotaFiscalEletronica::where('chave', $evento->chave)->where('status_nota', 100)->first();

            if (isset($nfe) and isset($nfe->xml)) {
                $stdCl = new Standardize(gzuncompress($nfe->xml));

                $arr = $stdCl->toArray();

                $result = searchValueInArray($arr, 'tpEvento');

                if ($result != '110111') {

                    $xml = Complements::cancelRegister(gzuncompress($nfe->xml), $evento->xml);

                    $nfe->update(['status_nota' => 101, 'xml' => gzcompress($xml)]);

                    DB::table('log_sefaz_nfe_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);

                    Log::warning('Nfe cancelada:' . $nfe->chave);
                }

                if ($result == '110111') {

                    DB::table('log_sefaz_nfe_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);
                }
            }
        }
    }
}
