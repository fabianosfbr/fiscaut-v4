<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\SefazNfseDownloadAndProcessBatchJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class DownloadNfseCommand extends Command
{
    protected $signature = 'sefaz:download-nfse 
                           {--issuer= : ID do emitente para download específico}
                           {--nsu= : NSU inicial para consulta}';

    protected $description = 'Baixa Notas Fiscais de Serviço Eletrônicas (NFSE) do SEFAZ';

    public function handle()
    {
        $this->info('Iniciando sincronização de NFSE com a SEFAZ');

        $issuerId = $this->option('issuer');
        $nsu = $this->option('nsu');

        if ($issuerId) {
            $issuer = Issuer::find($issuerId);
            if (! $issuer) {
                $this->error("Emitente {$issuerId} não encontrado. Nenhum job foi disparado.");

                return self::FAILURE;
            }

            SefazNfseDownloadAndProcessBatchJob::dispatch($issuer, $nsu);

            $this->info('Sincronização de NFSE com a SEFAZ concluída');

            return self::SUCCESS;
        }

        $issuers = Issuer::query()
            ->where('validade_certificado', '>', now())
            ->where('is_enabled', true)
            ->where('nfse_servico', true)
            ->get();

        if ($issuers->isEmpty()) {
            $this->error('Nenhum emitente válido encontrado. Nenhum job foi disparado.');

            return self::FAILURE;
        }

        foreach ($issuers as $issuer) {
            SefazNfseDownloadAndProcessBatchJob::dispatch($issuer, $nsu);
        }

        $this->info('Sincronização de NFSE com a SEFAZ concluída');

        return self::SUCCESS;
    }
}
