<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\SefazNfeDownloadAndProcessBatchJob;
use App\Models\Issuer;
use App\Models\LogSefazNfeContent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MonitoraNsuNfeFaltante extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitora-nsu-nfe-faltante
                                {--issuer= : ID do emitente para download específico}
                                {--nsu= : NSU inicial para consulta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitora NSU de NFe faltantes no SEFAZ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização de NFe com a SEFAZ');

        $issuerId = $this->option('issuer');
        $nsu = $this->option('nsu');

        $issuers = Issuer::where('validade_certificado', '>', now())
            ->where('is_enabled', true)
            ->where('nfe_servico', true)
            ->when($issuerId !== null, fn ($q) => $q->where('id', $issuerId))
            ->get();

        foreach ($issuers as $issuer) {

            $max = LogSefazNfeContent::where('issuer_id', $issuer->id)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->max('max_nsu');

            $min = LogSefazNfeContent::where('issuer_id', $issuer->id)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->min('nsu');

            if (isset($max) and isset($min)) {
                $nsus = LogSefazNfeContent::where('issuer_id', $issuer->id)
                    ->whereBetween('nsu', [$min, $max])
                    ->get()->pluck('nsu', 'id');

                for ($nsu = $min; $nsu < $max; $nsu++) {
                    if (! $nsus->contains($nsu)) {

                        SefazNfeDownloadAndProcessBatchJob::dispatch(issuer: $issuer, nsu: $nsu);

                        Log::info("NSU {$nsu} NFe recuperado para o emissor {$issuer->razao_social}");
                        break;
                    }
                }
            }
        }
    }
}
