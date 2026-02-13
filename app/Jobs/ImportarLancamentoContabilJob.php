<?php

namespace App\Jobs;

use App\Filament\Actions\Traits\ImportarLancamentoContabilTrait;
use App\Imports\OptimizedExcelImport;
use App\Models\JobProgress;
use App\Models\Layout;
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
        protected ?string $jobProgressId = null
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

        if (! $layout || ! $user) {
            $jobProgress?->update([
                'status' => 'failed',
                'message' => 'Layout ou Usuário não encontrado.',
            ]);

            return;
        }

        $filePath = Storage::disk('local')->path($this->relativePath);

        if (! file_exists($filePath)) {
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
            $excelData = $fileReader->getData();

            $jobProgress?->update([
                'progress' => 10,
                'message' => 'Processando dados...',
            ]);

            self::prepareData($excelData, $layout, $user, $jobProgress);

            $jobProgress?->update([
                'status' => 'done',
                'progress' => 100,
                'message' => 'Importação concluída com sucesso!',
            ]);

            Notification::make()
                ->success()
                ->title('Importação concluída')
                ->body('Todos os registros foram processados com sucesso.')
                ->sendToDatabase($user);
        } catch (Exception $e) {
            Log::error('Erro no Job ImportarLancamentoContabilJob: '.$e->getMessage());

            $jobProgress?->update([
                'status' => 'failed',
                'message' => 'Erro: '.$e->getMessage(),
            ]);

            throw $e;
        } finally {
            if (Storage::disk('local')->exists($this->relativePath)) {
                Storage::disk('local')->delete($this->relativePath);
            }
        }
    }
}
