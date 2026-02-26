<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\AutenticidadeNfeJob;
use App\Models\Issuer;
use App\Models\LogSefazNfseEvent;
use App\Models\NotaFiscalEletronica;
use App\Models\NotaFiscalServico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;

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
                ->where('chave_acesso',  $chave)
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
                ->get();

            foreach ($issuers as $issuer) {

                AutenticidadeNfeJob::dispatch($issuer);
            }
        }

        return Command::SUCCESS;
    }
}
