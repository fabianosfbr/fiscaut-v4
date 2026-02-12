<?php

namespace App\Console\Commands\Sefaz;

use App\Models\Issuer;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Models\LogSefazCteContent;
use Illuminate\Support\Facades\Log;
use App\Jobs\Sefaz\SefazCteDownloadAndProcessBatchJob;

class MonitoraNsuCteFaltante extends Command
{
     protected $signature = 'app:monitora-nsu-cte-faltante
                                {--issuer= : ID do emitente para download específico}
                                {--nsu= : NSU inicial para consulta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitora NSU de CTe faltantes no SEFAZ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização de CTe com a SEFAZ');

        $issuerId = $this->option('issuer');
        $nsu = $this->option('nsu');

        $issuers = Issuer::where('validade_certificado', '>', now())
            ->where('is_enabled', true)
            ->where('cte_servico', true)
            ->when($issuerId !== null, fn($q) => $q->where('id', $issuerId))
            ->get();

        foreach ($issuers as $issuer) {

            $max = LogSefazCteContent::where('issuer_id', $issuer->id)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->max('max_nsu');

            $min = LogSefazCteContent::where('issuer_id', $issuer->id)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->min('nsu');

            if (isset($max) and isset($min)) {
                $nsus = LogSefazCteContent::where('issuer_id', $issuer->id)
                    ->whereBetween('nsu', [$min, $max])
                    ->get()->pluck('nsu', 'id');

                for ($nsu = $min; $nsu < $max; $nsu++) {
                    if (!$nsus->contains($nsu)) {

                        SefazCteDownloadAndProcessBatchJob::dispatch(issuer: $issuer, nsu: $nsu);

                        Log::info("NSU {$nsu} CTe recuperado para o emissor {$issuer->razao_social}");
                        break;
                    }
                }
            }
        }
    }
}
