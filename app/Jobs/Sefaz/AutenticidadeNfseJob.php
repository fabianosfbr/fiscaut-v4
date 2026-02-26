<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\NotaFiscalServico;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutenticidadeNfseJob implements ShouldQueue
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

        $eventos = DB::table('log_sefaz_nfse_events')
            ->where('x_desc', 'like', '%cancelamento%')
            ->where('issuer_id', $this->issuer->id)
            ->where('dh_evento', '>=', $endDate)
            ->distinct()
            ->get();

        foreach ($eventos as $evento) {
            $nfse = NotaFiscalServico::where('chave_acesso', $evento->chave_acesso)
                ->where('cancelada', false)
                ->first();

            if (isset($nfse)) {
                $nfse->updateQuietly([
                    'cancelada' => true,
                ]);
            }

            Log::warning('Status da NFS-e atualizado para CANCELADA', [
                'chave_acesso' => $evento->chave_acesso,
                'issuer_id' => $evento->issuer_id,
            ]);
        }
    }
}
