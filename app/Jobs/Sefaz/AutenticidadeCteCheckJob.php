<?php

namespace App\Jobs\Sefaz;

use App\Models\ConhecimentoTransporteEletronico;
use App\Models\LogSefazCteEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NFePHP\CTe\Common\Standardize;
use NFePHP\CTe\Complements;

class AutenticidadeCteCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected LogSefazCteEvent $evento
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cte = ConhecimentoTransporteEletronico::where('chave', $this->evento->chave)
            ->where('status_cte', 100)
            ->first();

        if (! isset($cte) || ! isset($cte->xml)) {
            return;
        }

        $stdCl = new Standardize(gzuncompress($cte->xml));

        $arr = $stdCl->toArray();

        $result = searchValueInArray($arr, 'tpEvento');

         if ($result == '110111' || $result == '110112') {
            $xml = Complements::cancelRegister(gzuncompress($cte->xml), $this->evento->xml);

            $cte->update(['status_cte' => 101, 'xml' => gzcompress($xml)]);

            DB::table('log_sefaz_cte_events')
                ->where('id', $this->evento->id)
                ->update(['is_verificado_sefaz' => true]);

            Log::warning('Status da CT-e atualizado para CANCELADA', [
                'chave_acesso' => $this->evento->chave,
                'issuer_id' => $this->evento->issuer_id,
            ]);
        }
        
    }
}
