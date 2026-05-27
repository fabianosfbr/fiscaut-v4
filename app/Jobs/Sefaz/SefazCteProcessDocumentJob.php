<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\LogSefazCteContent;
use App\Models\User;
use App\Models\XmlImportJob;
use App\Services\Xml\XmlCteReaderService;
use App\Services\Xml\XmlIdentifierService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SefazCteProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $documento,
        protected Issuer $issuer,
        protected XmlImportJob $importJob
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Process the document based on its type
            switch ($this->documento['tipo_documento']) {
                case XmlIdentifierService::TIPO_CTE:
                    $this->processCteCompleta();
                    break;

                case XmlIdentifierService::TIPO_EVENTO_CTE:
                    $this->processEventoCte();
                    break;

                default:
                    Log::info('Tipo de documento não processado', [
                        'issuer_id' => $this->issuer->id,
                        'tipo' => $this->documento['tipo_documento'],
                        'nsu' => $this->documento['nsu'],
                    ]);
            }

            // Increment processed files counter on success
            $this->importJob->incrementProcessedFiles();

            // Check if all files have been processed
            $this->checkJobCompletion();
        } catch (\Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Erro ao processar documento da SEFAZ', [
            'issuer_id' => $this->issuer->id,
            'nsu' => $this->documento['nsu'] ?? 'N/A',
            'tipo' => $this->documento['tipo_documento'] ?? 'N/A',
            'error' => $exception->getMessage(),
        ]);

        $this->importJob->addError($exception->getMessage());

        // Increment processed files counter
        $this->importJob->incrementProcessedFiles();

        // Check if all files have been processed
        $this->checkJobCompletion();
    }

    /**
     * Processa NFe completa
     */
    private function processCteCompleta(): void
    {
        (new XmlCteReaderService)
            ->loadXml($this->documento['xml_content'])
            ->setOrigem('SEFAZ')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->registerLogContent($this->issuer, $this->documento['nsu'], $this->documento['max_nsu'], $this->documento['xml_content']);
        $this->importJob->incrementNumDocuments();
    }

    /**
     * Processa evento de CTe
     */
    private function processEventoCte(): void
    {
        (new XmlCteReaderService)
            ->loadXml($this->documento['xml_content'])
            ->setOrigem('SEFAZ')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->registerLogContent($this->issuer, $this->documento['nsu'], $this->documento['max_nsu'], $this->documento['xml_content']);
        $this->importJob->incrementNumEvents();
    }

    /**
     * Processa todas consultas de CTe
     */
    public function registerLogContent($issuer, $numnsu, $maxNSU, $xml): LogSefazCteContent
    {
        $logContent = LogSefazCteContent::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
            ],
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
                'max_nsu' => $maxNSU,
                'xml' => $xml,
            ]
        );

        Log::notice('CTe NSU consulta SEFAZ: '.$numnsu.' maxnsu: '.$maxNSU.' Emissor: '.$issuer->razao_social);

        return $logContent;
    }

    /**
     * Check if all documents in the import job have been processed.
     * Uses atomic DB update to prevent race conditions and double-notification.
     */
    protected function checkJobCompletion(): void
    {
        $jobId = $this->importJob->id;

        // Atomic check: only mark as completed if processed >= total and not already finished
        $updated = DB::table('xml_import_jobs')
            ->where('id', $jobId)
            ->where('processed_files', '>=', DB::raw('total_files'))
            ->whereNull('finished_at')
            ->update([
                'status' => XmlImportJob::STATUS_COMPLETED,
                'finished_at' => now(),
            ]);

        if ($updated > 0) {
            $this->sendCompletionNotification();
        }
    }

    /**
     * Send the completion notification to the issuer's owner user.
     */
    protected function sendCompletionNotification(): void
    {
        // Try to notify the issuer owner (if a user)
        $owner = $this->importJob->owner;
        if ($owner instanceof User) {
            $hasErrors = $this->importJob->error_files > 0;

            if ($hasErrors) {
                Notification::make()
                    ->warning()
                    ->title('Importação SEFAZ CTe concluída com erros')
                    ->body("{$this->importJob->imported_files} documentos importados, {$this->importJob->error_files} com erros.")
                    ->actions([
                        Action::make('view')
                            ->label('Ver detalhes')
                            ->button()
                            ->openUrlInNewTab()
                            ->url(route('filament.app.resources.xml-import-history.index', ['record' => $this->importJob->id])),
                    ])
                    ->sendToDatabase($owner, isEventDispatched: true);
            } else {
                Notification::make()
                    ->success()
                    ->title('Importação SEFAZ CTe concluída')
                    ->body('Todos os documentos SEFAZ foram processados com sucesso.')
                    ->actions([
                        Action::make('view')
                            ->label('Ver detalhes')
                            ->button()
                            ->openUrlInNewTab()
                            ->url(route('filament.app.resources.xml-import-history.index', ['record' => $this->importJob->id])),
                    ])
                    ->sendToDatabase($owner, isEventDispatched: true);
            }
        }
    }
}
