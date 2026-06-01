<?php

namespace App\Jobs\Sieg;

use App\Models\Issuer;
use App\Models\User;
use App\Models\XmlImportJob;
use App\Services\Xml\XmlNfseEventReaderService;
use App\Services\Xml\XmlNfseReaderService;
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

class ProcessDocumentNfseSiegJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    public function __construct(
        protected string $xml,
        protected Issuer $issuer,
        protected XmlImportJob $importJob
    ) {
        $this->onQueue('sieg');
    }

    public function handle(): void
    {
        try {
            if ($this->isEventXml()) {
                $this->processEvent();
            } else {
                $this->processDocument();
            }

            $this->importJob->incrementProcessedFiles();
            $this->checkJobCompletion();
        } catch (Throwable $e) {
            $this->failed($e);
        }
    }

    /**
     * Detecta se o XML é um evento (tag raiz <evento>) ou um documento NFSe (tag raiz <CompNFe>).
     */
    protected function isEventXml(): bool
    {
        $decoded = @gzdecode(base64_decode($this->xml));
        $rawXml = $decoded !== false ? $decoded : $this->xml;

        $simpleXml = simplexml_load_string($rawXml);
        if ($simpleXml === false) {
            return false;
        }

        return $simpleXml->getName() === 'evento';
    }

    /**
     * Processa um XML de documento NFSe (tag raiz <CompNFe>).
     */
    protected function processDocument(): void
    {
        (new XmlNfseReaderService)
            ->loadXml($this->xml)
            ->setIssuer($this->issuer)
            ->parse()
            ->save();
    }

    /**
     * Processa um XML de evento NFSe (tag raiz <evento>).
     */
    protected function processEvent(): void
    {
        (new XmlNfseEventReaderService)
            ->loadXml($this->xml)
            ->setIssuer($this->issuer)
            ->parse()
            ->save();
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('sieg_log')->error('Erro ao processar NFSe SIEG: '.$exception->getMessage().'XML: '.$this->xml);

        $this->importJob->addError($exception->getMessage());
        $this->importJob->incrementProcessedFiles();
        $this->checkJobCompletion();
    }

    protected function checkJobCompletion(): void
    {
        $jobId = $this->importJob->id;

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

    protected function sendCompletionNotification(): void
    {
        $owner = $this->importJob->owner;
        if ($owner instanceof User) {
            $hasErrors = $this->importJob->error_files > 0;

            if ($hasErrors) {
                Notification::make()
                    ->warning()
                    ->title('Importação SIEG NFSe concluída com erros')
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
                    ->title('Importação SIEG NFSe concluída')
                    ->body('Todos os documentos NFSe SIEG foram processados com sucesso.')
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
