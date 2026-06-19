<?php

namespace App\Jobs;

use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Services\ValidacaoTributaria\ValidacaoTributariaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidarTributacaoNfeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 30;

    public function __construct(
        protected NotaFiscalEletronica $nfe,
        protected ?Issuer $issuer = null,
    ) {}

    public function handle(ValidacaoTributariaService $service): void
    {
        $issuer = $this->issuer ?? currentIssuer();

        if ($issuer === null) {
            return;
        }

        $total = $service->validarEPersistir($this->nfe, $issuer);

        if ($total > 0) {
            logger()->info('Validação tributária concluída', [
                'nfe_id' => $this->nfe->id,
                'chave' => $this->nfe->chave,
                'inconsistencias' => $total,
            ]);
        }
    }
}
