<?php

namespace App\Jobs;

use App\Models\Issuer;
use App\Models\User;
use App\Models\XmlImportJob;
use App\Services\Xml\XmlCteReaderService;
use App\Services\Xml\XmlExtractorService;
use App\Services\Xml\XmlIdentifierService;
use App\Services\Xml\XmlNfceReaderService;
use App\Services\Xml\XmlNfeReaderService;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessXmlFile implements ShouldQueue
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
        protected string $fileKey,
        protected XmlImportJob $importJob,
        protected Issuer $issuer
    ) {
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Verifica se o arquivo existe antes de tentar processá-lo
            if (! Storage::disk('local')->exists($this->fileKey)) {
                $mensagemErro = 'Erro ao processar arquivo: O arquivo não existe no caminho '.$this->fileKey;
                $this->importJob->addError($mensagemErro);
                Log::error('Erro na importação de XML: '.$mensagemErro);

                return;
            }

            $filePath = Storage::disk('local')->path($this->fileKey);
            $extractor = new XmlExtractorService;
            $xmlContents = $extractor->extractFromPath($filePath);

            foreach ($xmlContents as $xmlContent) {
                $xmlContent = $xmlContent['content'];

                $tipoXml = XmlIdentifierService::identificarTipoXml($xmlContent);

                $serviceNfe = app(XmlNfeReaderService::class)
                    ->loadXml($xmlContent)
                    ->setOrigem('IMPORTADO')
                    ->setIssuer($this->issuer);
                $serviceCte = app(XmlCteReaderService::class)
                    ->loadXml($xmlContent)
                    ->setOrigem('IMPORTADO')
                    ->setIssuer($this->issuer);

                $serviceNfce = app(XmlNfceReaderService::class)
                    ->loadXml($xmlContent)
                    ->setOrigem('IMPORTADO')
                    ->setIssuer($this->issuer);

                // Processar conforme o tipo
                switch ($tipoXml) {
                    case XmlIdentifierService::TIPO_NFE:
                        $serviceNfe->parse()->save();

                        $this->importJob->incrementNumDocuments();
                        break;

                    case XmlIdentifierService::TIPO_NFCE:
                        $serviceNfce->parse()->save();

                        $this->importJob->incrementNumDocuments();
                        break;

                    case XmlIdentifierService::TIPO_NFE_RESUMO:
                        $serviceNfe->parse()->save();

                        $this->importJob->incrementNumEvents();
                        break;

                        // Processar evento de cancelamento de NF-e
                    case XmlIdentifierService::TIPO_EVENTO_NFE:
                        $serviceNfe->parse()->save();

                        $this->importJob->incrementNumEvents();
                        break;

                    case XmlIdentifierService::TIPO_CTE:
                        $serviceCte->parse()->save();

                        $this->importJob->incrementNumDocuments();
                        break;

                        // Processar evento de CT-e
                    case XmlIdentifierService::TIPO_EVENTO_CTE:
                        $serviceCte->parse()->save();

                        // Disparar evento de cancelamento
                        // event(new CteCancelada($event));

                        $this->importJob->incrementNumEvents();
                        break;

                    default:
                        throw new Exception('Tipo de XML não suportado: '.$tipoXml);
                }
            }

            // Increment counters atomically
            $this->importJob->incrementProcessedFiles();
            $this->importJob->incrementImportedFiles();

            // Check if all files have been processed
            $this->checkJobCompletion();
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $mensagemErro = 'Falha no processamento do arquivo: '.$exception->getMessage();
        $this->importJob->addError($mensagemErro);
        Log::error('Falha na importação de XML: '.$mensagemErro);

        // Mark the file as processed, but with errors
        $this->importJob->incrementProcessedFiles();

        // Check if all files have been processed
        $this->checkJobCompletion();
    }

    /**
     * Check if all files in the import job have been processed.
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
     * Send the completion notification to the user who initiated the import.
     */
    protected function sendCompletionNotification(): void
    {
        $user = User::find($this->importJob->user_id);
        if (! $user) {
            return;
        }

        $hasErrors = $this->importJob->error_files > 0;

        if ($hasErrors) {
            Notification::make()
                ->warning()
                ->title('Importação concluída com erros')
                ->body("{$this->importJob->imported_files} arquivos importados, {$this->importJob->error_files} com erros.")
                ->actions([
                    Action::make('view')
                        ->label('Ver detalhes')
                        ->button()
                        ->openUrlInNewTab()
                        ->url(route('filament.app.resources.xml-import-history.index', ['record' => $this->importJob->id])),
                ])
                ->sendToDatabase($user, isEventDispatched: true);
        } else {
            Notification::make()
                ->success()
                ->title('Importação concluída')
                ->body('Todos os arquivos XML foram processados com sucesso.')
                ->actions([
                    Action::make('view')
                        ->label('Ver detalhes')
                        ->button()
                        ->openUrlInNewTab()
                        ->url(route('filament.app.resources.xml-import-history.index', ['record' => $this->importJob->id])),
                ])
                ->sendToDatabase($user, isEventDispatched: true);
        }
    }
}
