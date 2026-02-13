<?php

namespace App\Jobs\Sieg;

use App\Models\Issuer;
use App\Models\User;
use App\Models\XmlImportJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessXmlSiegBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Tamanho do lote para processamento
     *
     * @var int
     */
    protected $batchSize = 50;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $xmlContents,
        protected XmlImportJob $importJob,
        protected Issuer $issuer
    ) {
        $this->onQueue('sieg');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Divide o array de XMLs em lotes menores para processamento
            $chunks = array_chunk($this->xmlContents, $this->batchSize);

            $jobs = [];
            foreach ($chunks as $chunk) {
                foreach ($chunk as $xmlBase64) {
                    // Decodificar o XML da base64
                    $xmlContent = base64_decode($xmlBase64);

                    // Criar um job para processar o XML
                    $jobs[] = new ProcessXmlSieg($xmlContent, $this->importJob, $this->issuer);
                }
            }

            $importJobId = $this->importJob->id;

            // Cria um batch de jobs para processamento

            Bus::batch($jobs)
                ->name('Processamento de XMLs SIEG')
                ->allowFailures()
                ->then(function (Batch $batch) use ($importJobId) {
                    // All jobs completed successfully
                    $importJob = XmlImportJob::find($importJobId);
                    if ($importJob) {
                        $importJob->updateQuietly([
                            'status' => XmlImportJob::STATUS_COMPLETED,
                            'total_files' => $importJob->total_files,
                        ]);

                        $jobId = $importJob->id;
                        $userId = $importJob->user_id;

                        // Dispatch um job separado para enviar a notificação
                        dispatch(function () use ($jobId, $userId) {
                            $user = User::find($userId);
                            if ($user) {
                                Notification::make()
                                    ->success()
                                    ->title('Importação concluída')
                                    ->body('Todos os arquivos XML foram processados com sucesso.')
                                    ->actions([
                                        Action::make('view')
                                            ->label('Ver detalhes')
                                            ->button()
                                            ->url(route('filament.admin.resources.xml-import-history.index', ['record' => $jobId])),
                                    ])
                                    ->sendToDatabase($user, isEventDispatched: true);
                            }
                        });
                    }
                })
                ->catch(function (Batch $batch, Throwable $e) use ($importJobId) {
                    $mensagemErro = 'Erro no processamento em lote: ' . $e->getMessage();
                    Log::error('Erro na importação em lote de XML: ' . $mensagemErro);

                    $importJob = XmlImportJob::find($importJobId);
                    if ($importJob) {
                        $importJob->addError($mensagemErro);
                        $importJob->updateQuietly([
                            'status' => XmlImportJob::STATUS_FAILED,
                        ]);

                        // Dispatch um job separado para enviar a notificação
                        $jobId = $importJob->id;
                        $userId = $importJob->user_id;
                        dispatch(function () use ($jobId, $userId, $e) {
                            $user = User::find($userId);
                            if ($user) {
                                Notification::make()
                                    ->danger()
                                    ->title('Erro')
                                    ->body('Ocorreu um erro ao processar a requisição: ' . $e->getMessage())
                                    ->actions([
                                        Action::make('view')
                                            ->label('Ver detalhes')
                                            ->button()
                                            ->url(route('filament.admin.resources.xml-import-history.index', ['record' => $jobId])),
                                    ])
                                    ->sendToDatabase($user, isEventDispatched: true);
                            }
                        });
                    }
                })
                ->finally(function (Batch $batch) {
                    // The batch has finished executing
                })
                ->dispatch();
        } catch (Throwable $e) {
            $this->failed($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $mensagemErro = 'Falha no processamento em lote: ' . $exception->getMessage();
        $this->importJob->addError($mensagemErro);
        $this->importJob->updateQuietly([
            'status' => XmlImportJob::STATUS_FAILED,
        ]);
        Log::error('Falha na importação em lote de XML: ' . $mensagemErro);
    }
}
