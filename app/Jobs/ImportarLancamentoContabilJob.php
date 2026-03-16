<?php

namespace App\Jobs;

use App\Filament\Actions\Traits\ImportarLancamentoContabilTrait;
use App\Imports\OptimizedExcelImport;
use App\Models\ImportarLancamentoContabil;
use App\Models\JobProgress;
use App\Models\Layout;
use App\Models\User;
use App\Services\Contabil\LayoutLancamentoResolverService;
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

class ImportarLancamentoContabilJob implements ShouldQueue
{
    use Batchable, Dispatchable, ImportarLancamentoContabilTrait, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $layoutId,
        protected string $relativePath,
        protected int $userId,
        protected string $jobProgressId
    ) {
        $this->onQueue('low');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $layout = Layout::find($this->layoutId);
        $user = User::find($this->userId);
        $jobProgress = $this->jobProgressId ? JobProgress::find($this->jobProgressId) : null;

        if (!$layout || !$user) {
            $jobProgress?->update([
                'status' => 'failed',
                'message' => 'Layout ou Usuário não encontrado.',
            ]);

            return;
        }

        $filePath = Storage::disk('local')->path($this->relativePath);

        if (!file_exists($filePath)) {
            Log::error("Job ImportarLancamentoContabilJob: Arquivo não encontrado em {$filePath}");

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

            $fileReader = (new OptimizedExcelImport($layout, $filePath));
            $rows = $fileReader->getData();

            $jobProgress?->update([
                'progress' => 10,
                'message' => 'Processando dados...',
            ]);

            $resolver = new LayoutLancamentoResolverService(
                $layout,
                (int) $layout->issuer_id,
                (int) $user->id,
                $jobProgress->id,
            );

            $resolvedRows = $resolver->resolveRows($rows);

            foreach ($resolvedRows as $resolved) {
                if (($resolved['valor'] ?? 0) == 0) {
                    continue;
                }

                $import = new ImportarLancamentoContabil();
                $import->issuer_id = $layout->issuer_id;
                $import->user_id = $user->id;
                $import->data = $resolved['data'] ?? null;
                $import->valor = $resolved['valor'] ?? 0;
                $import->debito = $resolved['debito'] ?? null;
                $import->credito = $resolved['credito'] ?? null;
                $import->is_exist = !is_null($resolved['data'] ?? null)
                    && !is_null($resolved['debito'] ?? null)
                    && !is_null($resolved['credito'] ?? null);
                $import->historico = $resolved['historico'] ?? ' ';
                $import->metadata = [
                    'descricao_debito' => $resolved['debito_descricao'] ?? null,
                    'descricao_credito' => $resolved['credito_descricao'] ?? null,
                    'cod_historico' => $resolved['cod_historico'] ?? null,
                    'col_historico' => $resolved['col_historico'] ?? null,
                    'row' => $resolved['metadata']['row'] ?? null,
                    'rule_trace' => $resolved['metadata']['rule_trace'] ?? null,
                    'type' => 'geral',
                ];
                $import->saveQuietly();
            }

            $jobProgress?->update([
                'status' => 'done',
                'progress' => 100,
                'message' => 'Importação concluída com sucesso!',
            ]);

            Notification::make()
                ->success()
                ->title('Importação concluída')
                ->body('Todos os registros foram processados com sucesso.')
                ->sendToDatabase($user, isEventDispatched: true);
        } catch (Exception $e) {
            Log::error('Erro no Job ImportarLancamentoContabilJob: ' . $e->getMessage());

            $jobProgress?->update([
                'status' => 'failed',
                'message' => 'Erro: ' . $e->getMessage(),
            ]);

            throw $e;
        } finally {
            if (Storage::disk('local')->exists($this->relativePath)) {
                Storage::disk('local')->delete($this->relativePath);
            }
        }
    }
}
