<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\SefazNfseDownloadAndProcessBatchJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class ConsultaNfseEmLote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-nfse-sefaz
                                {--issuer= : ID do emitente para download específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baixa Notas Fiscais de Serviço Eletrônicas (NFSE) do SEFAZ';

    public function handle()
    {
        $this->info('Iniciando sincronização de NFSE com a SEFAZ');
        $issuerId = $this->option('issuer');

        $issuers = Issuer::where('validade_certificado', '>', now())
            ->where('is_enabled', true)
            ->where('nfse_servico', true)
            ->when($issuerId !== null, fn ($q) => $q->where('id', $issuerId))
            ->get();

        foreach ($issuers as $issuer) {
            SefazNfseDownloadAndProcessBatchJob::dispatch($issuer);
        }

        $this->info('Sincronização de NFSE com a SEFAZ concluída');

        return self::SUCCESS;
    }
}
