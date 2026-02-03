<?php

namespace App\Listeners;

use App\Enums\StatusCteEnum;
use App\Events\CteCancelada;
use App\Models\ConhecimentoTransporteEletronico;
use App\Services\Xml\XmlIdentifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use NFePHP\CTe\Complements;

class AtualizarStatusCteCancelada implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CteCancelada $event): void
    {

        try {
            // Busca o conhecimento de transporte pela chave de acesso
            $cte = ConhecimentoTransporteEletronico::where('chave_acesso', $event->event->chave)
                ->where('status_cte', '!=', StatusCteEnum::CANCELADA)
                ->first();

            if ($cte) {

                $detalhes = XmlIdentifierService::obterDetalhesEvento($event->event->xml);

                if ($detalhes['tpEvento'] == 110111) {
                    $xml = Complements::cancelRegister($cte->xml_content, $event->event->xml);
                    $cte->updateQuietly([
                        'xml_content' => $xml,
                        'status_cte' => StatusCteEnum::CANCELADA,
                    ]);
                }

                Log::info('Status do CTE atualizado para CANCELADA', [
                    'chave_acesso' => $event->event->chave,
                    'issuer_id' => $event->event->issuer_id,
                    'tenant_id' => $event->event->tenant_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status do CTE cancelada', [
                'chave_acesso' => $event->event->chave,
                'error' => $e->getMessage(),
                'issuer_id' => $event->event->issuer_id,
                'tenant_id' => $event->event->tenant_id,
            ]);

            throw $e;
        }
    }
}
