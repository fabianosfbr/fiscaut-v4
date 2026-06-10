<?php

namespace App\Console\Commands\Sieg;

use App\Enums\XmlImportJobType;
use App\Jobs\Sieg\SiegConnect;
use App\Models\Issuer;
use App\Models\XmlImportJob;
use Illuminate\Console\Command;

class SyncNfe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-nfe-sieg 
                    {--issuer= : ID do emitente para download específico}
                    {--start= : Data de início do tratamento (YYYY-MM-DD)}
                    {--end= : Data de término do tratamento (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza NFes com o Sieg';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $issuerId = $this->option('issuer');

        $start = $this->option('start');
        $end = $this->option('end');

        $end = is_string($end) && $end !== '' ? $end : now()->format('Y-m-d');
        $start = is_string($start) && $start !== '' ? $start : now()->toImmutable()->subDay(4)->format('Y-m-d');

        $issuers = Issuer::with('tenant')
            ->where('is_enabled', true)
            ->where('sync_sieg', true)
            ->when($issuerId !== null, fn($q) => $q->where('id', $issuerId))
            ->get();

        foreach ($issuers as $issuer) {
            $importJob = $this->createImportJob($issuer);

            $cnpjTypes = ['CnpjEmit', 'CnpjDest'];
            foreach ($issuers as $issuer) {
                $importJob = $this->createImportJob($issuer);
                foreach ([true, false] as $event) {
                    foreach ($cnpjTypes as $tipoCnpj) {
                        SiegConnect::dispatch(
                            tipoDocumento: 1,  //  tipo documento
                            tipoCnpj: $tipoCnpj,  // Tipo CNPJ
                            dataInicial: $start,
                            dataFinal: $end,
                            issuerId: $issuer->id,
                            importJobId: $importJob->id,
                            event: $event,
                        )->onQueue('sieg');
                    }
                }
            }
        }

        $this->info('Sincronização de documentos SIEG para NFes emitidas e recebidas em lote concluída nas datas de ' . $start . ' a ' . $end);

        return self::SUCCESS;
    }

    private function createImportJob(Issuer $issuer): XmlImportJob
    {
        return XmlImportJob::createQuietly([
            'tenant_id' => $issuer->tenant_id,
            'issuer_id' => $issuer->id,
            'owner_type' => $issuer::class,
            'owner_id' => $issuer->id,
            'import_type' => XmlImportJobType::SIEG,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => 0,
            'errors' => [],
        ]);
    }
}
