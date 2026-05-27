<?php

namespace App\Jobs\Sefaz;

use App\Models\LogSefazNfseEvent;
use App\Models\NotaFiscalServico;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutenticidadeNfseCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected LogSefazNfseEvent $evento
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $nfse = NotaFiscalServico::where('chave_acesso', $this->evento->chave_acesso)
            ->where('cancelada', false)
            ->first();

        if (isset($nfse)) {
            $nfse->updateQuietly([
                'cancelada' => true,
            ]);
        }

        Log::warning('Status da NFS-e atualizado para CANCELADA', [
            'chave_acesso' => $this->evento->chave_acesso,
            'issuer_id' => $this->evento->issuer_id,
        ]);
    }
}
