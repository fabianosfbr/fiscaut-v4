<?php

namespace App\Console\Commands\Sefaz;

use App\Models\Issuer;
use NFePHP\NFe\Complements;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use NFePHP\NFe\Common\Standardize;
use Illuminate\Support\Facades\Log;
use App\Jobs\Sefaz\AutenticidadeCteJob;
use App\Models\ConhecimentoTransporteEletronico;

class CheckAutenticidadeCte extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-autenticidade-cte {--chave=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se algum cte foi cancelado';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $chave = $this->option('chave');

        if (isset($chave)) {

            $cte = ConhecimentoTransporteEletronico::where('chave', $chave)->where('status_nota', 100)->first();

            $evento = DB::table('log_sefaz_cte_events')->where('tp_evento', 110111)->where('chave', '>=', $chave)->first();

            if (isset($cte) and isset($cte->xml) and isset($evento)) {
                $stdCl = new Standardize(gzuncompress($cte->xml));

                $arr = $stdCl->toArray();

                $result = searchValueInArray($arr, 'tpEvento');

                if ($result != '110111') {

                    $xml = Complements::cancelRegister(gzuncompress($cte->xml), $evento->xml);

                    $cte->update(['status_nota' => 101, 'xml' => gzcompress($xml)]);

                    Log::warning('Nfe cancelada:' . $cte->chave);
                    dump('Nfe cancelada:' . $cte->chave);
                }
            }
        } else {
            $issuers = Issuer::where('validade_certificado', '>', now())
                ->where('is_enabled', true)
                ->get();


            foreach ($issuers as $issuer) {

                AutenticidadeCteJob::dispatch($issuer);
            }
        }



        return Command::SUCCESS;
    }
}
