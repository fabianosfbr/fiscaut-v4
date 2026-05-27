<?php

namespace App\Listeners;

use App\Enums\StatusNfeEnum;
use App\Events\NfceCancelada;
use App\Models\NotaFiscalConsumidor;
use App\Services\Xml\XmlIdentifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Complements;

class AtualizarStatusNfceCancelada implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NfceCancelada $event): void
    {

        try {
            // Busca a nota fiscal pela chave de acesso
            $notaFiscal = NotaFiscalConsumidor::where('chave', $event->event->chave)
                ->where('status_nota', '!=', StatusNfeEnum::CANCELADA)
                ->first();

            if ($notaFiscal) {

                $detalhes = XmlIdentifierService::obterDetalhesEvento($event->event->xml);

                if ($detalhes['tpEvento'] == 110111) {
                    $xml = Complements::cancelRegister(gzuncompress($notaFiscal->xml), $event->event->xml);
                    $notaFiscal->updateQuietly([
                        'xml' => gzcompress($xml),
                        'status_nota' => StatusNfeEnum::CANCELADA,
                    ]);
                }

                Log::info('Status da Nfce atualizado para CANCELADA', [
                    'chave' => $event->event->chave,
                    'issuer_id' => $event->event->issuer_id,
                    'tenant_id' => $event->event->tenant_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status da Nfce cancelada', [
                'chave' => $event->event->chave,
                'error' => $e->getMessage(),
                'issuer_id' => $event->event->issuer_id,
                'tenant_id' => $event->event->tenant_id,
            ]);

            throw $e;
        }
    }
}
