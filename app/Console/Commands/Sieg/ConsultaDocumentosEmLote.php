<?php

namespace App\Console\Commands\Sieg;

use App\Models\Issuer;
use App\Models\XmlImportJob;
use App\Jobs\Sieg\SiegConnect;
use App\Enums\XmlImportJobType;
use Illuminate\Console\Command;

class ConsultaDocumentosEmLote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-documentos-sieg 
                    {--issuer= : ID do emitente para download específico}
                    {--start= : Data de início do tratamento (YYYY-MM-DD)}
                    {--end= : Data de término do tratamento (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar documentos SIEG em lote';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização de documentos SIEG em lote');

        $issuerId = $this->option('issuer');

        $start = $this->option('start');
        $end = $this->option('end');


        $end = is_string($end) && $end !== '' ? $end : now()->format('Y-m-d');
        $start = is_string($start) && $start !== '' ? $start : now()->toImmutable()->subDay(2)->format('Y-m-d');


        $issuers = Issuer::with('tenant')
            ->where('is_enabled', true)
            ->when($issuerId !== null, fn($q) => $q->where('id', $issuerId))
            ->get();

        foreach ($issuers as $issuer) {

            $importJob = $this->createImportJob($issuer);

            SiegConnect::dispatch(
                1, //  tipo documento
                'CnpjEmit', // Tipo CNPJ                
                $start,
                $end,
                $issuer->id,
                $importJob->id,
            );

            SiegConnect::dispatch(
                1, //  tipo documento
                'CnpjDest', // Tipo CNPJ                
                $start,
                $end,
                $issuer->id,
                $importJob->id,
            );


            SiegConnect::dispatch(
                2, //  tipo documento
                'CnpjEmit', // Tipo CNPJ                
                $start,
                $end,
                $issuer->id,
                $importJob->id,
            );

            SiegConnect::dispatch(
                2, //  tipo documento
                'CnpjDest', // Tipo CNPJ                
                $start,
                $end,
                $issuer->id,
                $importJob->id,
            );

            SiegConnect::dispatch(
                2, //  tipo documento
                'CnpjRem', // Tipo CNPJ                
                $start,
                $end,
                $issuer->id,
                $importJob->id,
            );

            SiegConnect::dispatch(
                2, //  tipo documento
                'CnpjTom', // Tipo CNPJ                
                $start,
                $end,
                $issuer->id,
                $importJob->id,
            );
        }

        $this->info('Sincronização de documentos SIEG em lote concluída');

        return self::SUCCESS;
    }

    private function createImportJob(Issuer $issuer): XmlImportJob
    {
        return  XmlImportJob::createQuietly([
            'tenant_id' => $issuer->tenant_id,
            'issuer_id' => $issuer->id,
            'owner_type' => $issuer::class,
            'owner_id' => $issuer->id,
            'import_type' => XmlImportJobType::SYSTEM,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => 0,
            'errors' => [],
        ]);
    }
}
