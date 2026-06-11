<?php

namespace App\Console\Commands\Sieg;

use App\Enums\XmlImportJobType;
use App\Jobs\Sieg\SiegConnect;
use App\Models\Issuer;
use App\Models\XmlImportJob;
use Illuminate\Console\Command;

class SyncCte extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-cte-sieg 
                    {--issuer= : ID do emitente para download específico}
                    {--start= : Data de início do tratamento (YYYY-MM-DD)}
                    {--end= : Data de término do tratamento (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza Ctes com o Sieg';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $issuerId = $this->option('issuer');

        $start = $this->option('start');
        $end = $this->option('end');

        $end = is_string($end) && $end !== '' ? $end : now()->format('Y-m-d');
        $start = is_string($start) && $start !== '' ? $start : now()->toImmutable()->subDay(2)->format('Y-m-d');

        $issuers = Issuer::with('tenant')
            ->where('is_enabled', true)
            ->where('sync_sieg', true)
            ->when($issuerId !== null, fn ($q) => $q->where('id', $issuerId))
            ->get();

        $cnpjTypes = ['CnpjEmit', 'CnpjDest', 'CnpjTom', 'CnpjRem'];

        foreach ($issuers as $issuer) {
            $importJob = $this->createImportJob($issuer);

            foreach ([true] as $event) {
                foreach ($cnpjTypes as $tipoCnpj) {
                    SiegConnect::dispatch(
                        tipoDocumento: 2,  //  tipo documento
                        tipoCnpj: $tipoCnpj,  // Tipo CNPJ
                        dataInicial: $start,
                        dataFinal: $end,
                        issuerId: $issuer->id,
                        importJobId: $importJob->id,
                        event: $event,
                    )->onQueue('high');

                    $this->info('Sincronizando documentos SIEG para Ctes '.$tipoCnpj.' '.($event ? ' com evento' : ' sem evento').' para '.$issuer->razao_social);

                    sleep(3);  //  3 segundos
                }
            }
        }

        $this->info('Sincronização de documentos SIEG para Ctes em lote concluída nas datas de '.$start.' a '.$end);

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
