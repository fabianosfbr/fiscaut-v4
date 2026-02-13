<?php

namespace App\Jobs;

use App\Models\Issuer;
use App\Models\User;
use App\Models\XmlImportJob;
use Exception;
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

class ProcessXmlFileBatch implements ShouldQueue
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
        protected XmlImportJob $importJob
    ) {
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $issuer = Issuer::find($this->importJob->issuer_id);
            if (! $issuer) {
                throw new Exception('Empresa não encontrada');
            }
            // Divide o array de XMLs em lotes menores para processamento
            $chunks = array_chunk($this->xmlContents, $this->batchSize);

            $jobs = [];
            foreach ($chunks as $chunk) {
                foreach ($chunk as $xmlContent) {
                    Log::info('Processando XML', ['xmlContent' => $xmlContent]);

                    // Criar um job para processar o XML
                    $jobs[] = new ProcessXmlFile($xmlContent, $this->importJob, $issuer);
                }
            }
            $importJobId = $this->importJob->id;

            // Cria um batch de jobs para processamento

            Bus::batch($jobs)
                ->name('Processamento de XMLs')
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
                                            ->openUrlInNewTab()
                                            ->url(route('filament.admin.resources.xml-import-history.index', ['record' => $jobId])),
                                    ])
                                    ->sendToDatabase($user);
                            }
                        });
                    }
                })
                ->catch(function (Batch $batch, Throwable $e) use ($importJobId) {
                    $mensagemErro = 'Erro no processamento em lote: '.$e->getMessage();
                    Log::error('Erro na importação em lote de XML: '.$mensagemErro);

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
                                    ->body('Ocorreu um erro ao processar a requisição: '.$e->getMessage())
                                    ->actions([
                                        Action::make('view')
                                            ->label('Ver detalhes')
                                            ->button()
                                            ->openUrlInNewTab()
                                            ->url(route('filament.admin.resources.xml-import-history.index', ['record' => $jobId])),
                                    ])
                                    ->sendToDatabase($user);
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
    public function failed(Throwable $exception): void
    {
        $mensagemErro = 'Falha no processamento em lote: '.$exception->getMessage();
        $this->importJob->addError($mensagemErro);
        $this->importJob->updateQuietly([
            'status' => XmlImportJob::STATUS_FAILED,
        ]);
        Log::error('Falha na importação em lote de XML: '.$mensagemErro);
    }
}
