<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\AutenticidadeNfceJob;
use App\Models\Issuer;
use App\Models\NotaFiscalConsumidor;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;

class CheckAutenticidadeNfce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-autenticidade-nfce {--chave=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se alguma nfce foi cancelada';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $chave = $this->option('chave');

        if (isset($chave)) {
            $nfe = NotaFiscalConsumidor::where('chave', $chave)->where('status_nota', 100)->first();

            $evento = DB::table('log_sefaz_nfe_events')
                ->where('tp_evento', 110111)
                ->where('chave', $chave)
                ->first();

            if (isset($nfe) and isset($nfe->xml) and isset($evento)) {
                $stdCl = new Standardize(gzuncompress($nfe->xml));

                $arr = $stdCl->toArray();

                $result = searchValueInArray($arr, 'tpEvento');

                if ($result != '110111') {
                    $xml = Complements::cancelRegister(gzuncompress($nfe->xml), $evento->xml);

                    $nfe->update(['status_nota' => 101, 'xml' => gzcompress($xml)]);

                    Log::warning('Nfce cancelada:'.$nfe->chave);
                }
            }
        } else {
            $issuers = Issuer::where('validade_certificado', '>', now())
                ->where('is_enabled', true)
                ->where('nfce_servico', true)
                ->get();

            $this->info('Total de '.$issuers->count().' empresas serão processadas');

            $retentionDays = config('admin.schedule_antenticidate_days', 30);

            $endDate = Carbon::now()->subDays($retentionDays);

            foreach ($issuers as $issuer) {
                $this->info('Empresa '.$issuer->id.' - '.$issuer->razao_social.' empresas serão processadas. Status do serviço: '.$issuer->cte_servico);

                // AutenticidadeNfceJob::dispatch($issuer);

                $eventos = DB::table('log_sefaz_nfe_events')
                    ->where('tp_evento', 110111)
                    ->where('dh_evento', '>=', $endDate)
                    ->where('is_verificado_sefaz', false)
                    ->where('issuer_id', $issuer->id)
                    ->distinct()
                    ->get();

                foreach ($eventos as $evento) {
                    $nfe = NotaFiscalConsumidor::where('chave', $evento->chave)->first();

                    if ($nfe && $nfe->status_nota == 100) {
                        $xml = Complements::cancelRegister(gzuncompress($nfe->xml), $evento->xml);

                        $nfe->update(['status_nota' => 101, 'xml' => gzcompress($xml)]);

                        DB::table('log_sefaz_nfe_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);

                        Log::warning('Nfce cancelada: '.$nfe->chave);
                    }

                    if ($nfe && $nfe->status_nota == 101) {
                        DB::table('log_sefaz_nfe_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
