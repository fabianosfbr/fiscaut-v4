<?php

namespace App\Listeners;

use App\Enums\StatusNfeEnum;
use App\Events\NfseCancelada;
use App\Models\NotaFiscalEletronica;
use App\Models\NotaFiscalServico;
use App\Services\Xml\XmlIdentifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Complements;

class AtualizarStatusNfseCancelada implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NfseCancelada $event): void
    {

        try {
            // Busca a nota fiscal pela chave de acesso
            $notaFiscal = NotaFiscalServico::where('chave', $event->event->chave)
                ->where('cancelada', false)
                ->first();

            if ($notaFiscal) {

                if (str_contains(strtolower($event->event->x_desc), 'cancelamento')) {
                    
                    $notaFiscal->updateQuietly([
                        'cancelada' => true,
                    ]);
                }

                Log::info('Status da NFS-e atualizado para CANCELADA', [
                    'chave_acesso' => $event->event->chave,
                    'issuer_id' => $event->event->issuer_id,
                    'tenant_id' => $event->event->tenant_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status da NFe cancelada', [
                'chave' => $event->event->chave,
                'error' => $e->getMessage(),
                'issuer_id' => $event->event->issuer_id,
                'tenant_id' => $event->event->tenant_id,
            ]);

            throw $e;
        }
    }
}
