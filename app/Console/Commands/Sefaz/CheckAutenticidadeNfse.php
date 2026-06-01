<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\AutenticidadeNfseJob;
use App\Models\Issuer;
use App\Models\LogSefazNfseEvent;
use App\Models\NotaFiscalServico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class CheckAutenticidadeNfse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-autenticidade-nfse {--chave=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se alguma nfse foi cancelada';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $chave = $this->option('chave');

        if (isset($chave)) {
            $nfse = NotaFiscalServico::where('chave_acesso', $chave)->where('cancelada', false)->first();

            $evento = LogSefazNfseEvent::where('x_desc', 'like', '%cancelamento%')
                ->where('chave_acesso', $chave)
                ->first();

            if (isset($nfse) and isset($evento)) {
                $nfse->updateQuietly([
                    'cancelada' => true,
                ]);

                Log::warning('Status da NFS-e atualizado para CANCELADA', [
                    'chave_acesso' => $evento->chave_acesso,
                    'issuer_id' => $evento->issuer_id,
                ]);
            }
        } else {
            $issuers = Issuer::where('validade_certificado', '>', now())
                ->where('is_enabled', true)
                ->where('nfse_servico', true)
                ->get();

            $this->info('Total de ' . $issuers->count() . ' empresas serão processadas');

            $retentionDays = config('admin.schedule_antenticidate_days', 30);

            $endDate = Carbon::now()->subDays($retentionDays);

            foreach ($issuers as $issuer) {
                // AutenticidadeNfseJob::dispatch($issuer);
                $this->info('Empresa ' . $issuer->id . ' - ' . $issuer->razao_social . ' empresas serão processadas. Status do serviço: ' . $issuer->nfse_servico);

                $eventos = DB::table('log_sefaz_nfse_events')
                    ->where('x_desc', 'like', '%cancelamento%')
                    ->where('issuer_id', $issuer->id)
                    ->where('dh_evento', '>=', $endDate)
                    ->where('is_verificado_sefaz', false)
                    ->distinct()
                    ->get();

                foreach ($eventos as $evento) {
                    $nfse = NotaFiscalServico::where('chave_acesso', $evento->chave_acesso)->first();

             

                    if (isset($nfse) && $nfse->cancelada == false) {
                        $nfse->updateQuietly([
                            'cancelada' => true,
                        ]);

                        DB::table('log_sefaz_nfse_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);
                        Log::warning('Nfse cancelada: ' . $nfse->chave_acesso);
                    }

                    if ((isset($nfse) && $nfse->cancelada == true)) {
                        DB::table('log_sefaz_nfse_events')->where('id', $evento->id)->update(['is_verificado_sefaz' => true]);
                    }
                           
                }
            }
        }

        return Command::SUCCESS;
    }
}
