<?php

namespace App\Listeners;

use App\Enums\StatusNfeEnum;
use App\Events\NfeCancelada;
use App\Models\NotaFiscalEletronica;
use App\Services\Xml\XmlIdentifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Complements;

class AtualizarStatusNfeCancelada implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NfeCancelada $event): void
    {

        try {
            // Busca a nota fiscal pela chave de acesso
            $notaFiscal = NotaFiscalEletronica::where('chave', $event->event->chave)
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

                Log::info('Status da NFe atualizado para CANCELADA', [
                    'chave_acesso' => $event->event->chave,
                    'issuer_id' => $event->event->issuer_id,
                    'tenant_id' => $event->event->tenant_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status da NFe cancelada', [
                'chave_acesso' => $event->event->chave,
                'error' => $e->getMessage(),
                'company_id' => $event->event->company_id,
                'tenant_id' => $event->event->tenant_id,
            ]);

            throw $e;
        }
    }
}
