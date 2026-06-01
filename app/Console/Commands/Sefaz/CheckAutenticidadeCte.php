<?php

namespace App\Console\Commands\Sefaz;

use App\Models\ConhecimentoTransporteEletronico;
use App\Models\Issuer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use NFePHP\CTe\Complements;
use NFePHP\NFe\Common\Standardize;

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
                ->where('cte_servico', true)
                ->get();

            $this->info('Total de ' . $issuers->count() . ' empresas serão processadas');

            $retentionDays = config('admin.schedule_antenticidate_days', 30);

            $endDate = Carbon::now()->subDays($retentionDays);
            foreach ($issuers as $issuer) {
                $this->info('Empresa ' . $issuer->id . ' - ' . $issuer->razao_social . ' empresas serão processadas. Status do serviço: ' . $issuer->cte_servico);

                // AutenticidadeCteJob::dispatch($issuer);

                $eventos = DB::table('log_sefaz_cte_events')
                    ->where('tp_evento', 110111)
                    ->where('dh_evento', '>=', $endDate)
                    ->where('is_verificado_sefaz', false)
                    ->where('issuer_id', $issuer->id)
                    ->distinct()
                    ->get();

                foreach ($eventos as $evento) {
                    $cte = ConhecimentoTransporteEletronico::where('chave', $evento->chave)->first();
                    
                    if ($cte && $cte->status_cte->value == 100) {
                        $xml = Complements::cancelRegister(gzuncompress($cte->xml), $evento->xml);

                        $cte->update(['status_cte' => 101, 'xml' => gzcompress($xml)]);

                        DB::table('log_sefaz_cte_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);

                        Log::warning('CTe cancelada: ' . $cte->chave);
                    }
                    if ($cte && $cte->status_cte->value == 101) {
                        DB::table('log_sefaz_cte_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
