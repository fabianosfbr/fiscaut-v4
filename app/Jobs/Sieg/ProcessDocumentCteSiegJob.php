<?php

namespace App\Jobs\Sieg;

use App\Models\Issuer;
use App\Models\User;
use App\Models\XmlImportJob;
use App\Services\Xml\XmlCteReaderService;
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

class ProcessDocumentCteSiegJob implements ShouldQueue
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
            (new XmlCteReaderService)
                ->loadXml($this->xml)
                ->setOrigem('SIEG')
                ->setIssuer($this->issuer)
                ->parse()
                ->save();

            Log::channel('sieg_log')->info('CTe da empresa '.$this->issuer->razao_social.' processada com sucesso: '.$this->xml);
            $this->importJob->incrementProcessedFiles();
            $this->checkJobCompletion();
        } catch (Throwable $e) {
            $this->failed($e);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('sieg_log')->error('Erro ao processar CTe SIEG: '.$exception->getMessage().'XML: '.$this->xml);

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
                    ->title('Importação SIEG CTe concluída com erros')
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
                    ->title('Importação SIEG CTe concluída')
                    ->body('Todos os documentos CTe SIEG foram processados com sucesso.')
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
