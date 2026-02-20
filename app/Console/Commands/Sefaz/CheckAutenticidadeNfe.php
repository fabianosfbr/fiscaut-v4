<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\AutenticidadeNfeJob;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;

class CheckAutenticidadeNfe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-autenticidade-nfe {--chave=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se alguma nfe foi cancelada';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $chave = $this->option('chave');

        if (isset($chave)) {

            $nfe = NotaFiscalEletronica::where('chave', $chave)->where('status_nota', 100)->first();

            $evento = DB::table('log_sefaz_nfe_events')
                ->where('tp_evento', 110111)
                ->where('chave', '>=', $chave)->first();

            if (isset($nfe) and isset($nfe->xml) and isset($evento)) {
                $stdCl = new Standardize(gzuncompress($nfe->xml));

                $arr = $stdCl->toArray();

                $result = searchValueInArray($arr, 'tpEvento');

                if ($result != '110111') {

                    $xml = Complements::cancelRegister(gzuncompress($nfe->xml), $evento->xml);

                    $nfe->update(['status_nota' => 101, 'xml' => gzcompress($xml)]);

                    Log::warning('Nfe cancelada:'.$nfe->chave);
                    dump('Nfe cancelada:'.$nfe->chave);
                }
            }
        } else {
            $issuers = Issuer::where('validade_certificado', '>', now())
                ->where('is_enabled', true)
                ->get();

            foreach ($issuers as $issuer) {

                AutenticidadeNfeJob::dispatch($issuer);
            }
        }

        return Command::SUCCESS;
    }
}
