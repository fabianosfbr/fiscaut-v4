<?php

namespace App\Jobs;

use App\Imports\OptimizedExcelSuperLogicaImport;
use App\Models\ImportarLancamentoContabil;
use App\Models\JobProgress;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportarLancamentoContabilSuperLogicaJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $relativePath,
        protected int $userId,
        protected int $issuerId,
        protected ?string $jobProgressId = null
    ) {
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        $jobProgress = $this->jobProgressId ? JobProgress::find($this->jobProgressId) : null;

        if (! $user) {
            $jobProgress?->update([
                'status' => 'failed',
                'message' => 'Usuário não encontrado.',
            ]);

            return;
        }

        $filePath = Storage::disk('local')->path($this->relativePath);

        if (! file_exists($filePath)) {
            Log::error("Job ImportarLancamentoContabilSuperLogicaJob: Arquivo não encontrado em {$filePath}");

            $jobProgress?->update([
                'status' => 'failed',
                'message' => 'Arquivo não encontrado.',
            ]);

            return;
        }

        try {
            $jobProgress?->update([
                'status' => 'running',
                'progress' => 0,
                'message' => 'Lendo arquivo Excel...',
            ]);

            $fileReader = new OptimizedExcelSuperLogicaImport($filePath);
            $rows = $fileReader->import();

            if (empty($rows)) {
                $jobProgress?->update([
                    'status' => 'failed',
                    'message' => 'O arquivo Excel está vazio.',
                ]);

                Notification::make()
                    ->warning()
                    ->title('Arquivo vazio')
                    ->body('O arquivo Excel não contém dados para importação.')
                    ->sendToDatabase($user, isEventDispatched: true);

                return;
            }

            $jobProgress?->update([
                'progress' => 10,
                'message' => 'Preparando dados para importação...',
            ]);

            // Prepara os dados com parâmetros do SuperLogica
            $preparedRows = $fileReader->prepareData($rows, $this->issuerId);

            $jobProgress?->update([
                'progress' => 20,
                'message' => 'Processando registros...',
            ]);

            $totalRows = count($preparedRows);
            $processedCount = 0;
            $errorCount = 0;

            foreach ($preparedRows as $index => $row) {
                $rowNumber = $index + 1;

                // Atualiza o progresso a cada 10 linhas ou no final
                if ($jobProgress && ($rowNumber % 10 === 0 || $rowNumber === $totalRows)) {
                    $percentage = 20 + (int) (($rowNumber / $totalRows) * 70); // 20% a 90%
                    $jobProgress->update([
                        'progress' => $percentage,
                        'message' => "Processando linha {$rowNumber} de {$totalRows}...",
                    ]);
                }

                try {
                    $import = new ImportarLancamentoContabil;
                    $import->issuer_id = $this->issuerId;
                    $import->user_id = $this->userId;
                    $import->data = $row['credito'] ?? $row['liquidacao'];
                    $import->valor = abs($row['valor']);
                    $import->debito = $row['conta_debito'];
                    $import->credito = $row['conta_credito'];
                    $import->is_exist = true;
                    $import->historico = $row['historico'] ?? null;
                    $import->metadata = [
                        'codigo_historico' => $row['codigo_historico'] ?? null,
                        'row' => $row,
                        'type' => 'super_logica',
                    ];

                    $import->saveQuietly();
                    $processedCount++;
                } catch (Exception $e) {
                    $errorCount++;
                    Log::warning("Erro ao processar linha {$rowNumber}: ".$e->getMessage());
                }
            }

            $jobProgress?->update([
                'status' => 'done',
                'progress' => 100,
                'message' => "Importação concluída! {$processedCount} registros processados.",
            ]);

            Notification::make()
                ->success()
                ->title('Importação concluída')
                ->body("Foram processados {$processedCount} registros com sucesso.".($errorCount > 0 ? " {$errorCount} linhas com erro." : ''))
                ->sendToDatabase($user, isEventDispatched: true);
        } catch (Exception $e) {
            Log::error('Erro no Job ImportarLancamentoContabilSuperLogicaJob: '.$e->getMessage());

            $jobProgress?->update([
                'status' => 'failed',
                'message' => 'Erro: '.$e->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Erro na Importação')
                ->body('Ocorreu um erro durante o processamento: '.$e->getMessage())
                ->sendToDatabase($user, isEventDispatched: true);

            throw $e;
        } finally {
            if (Storage::disk('local')->exists($this->relativePath)) {
                Storage::disk('local')->delete($this->relativePath);
            }
        }
    }
}
