<?php

namespace App\Jobs;

use Exception;
use App\Models\User;
use App\Models\Layout;
use Illuminate\Support\Facades\Log;
use App\Imports\OptimizedExcelImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Filament\Actions\Traits\ImportarLancamentoContabilTrait;
use Illuminate\Bus\Batchable;

class ImportarLancamentoContabilJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ImportarLancamentoContabilTrait, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $layoutId,
        protected string $relativePath,
        protected int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $layout = Layout::find($this->layoutId);
        $user = User::find($this->userId);

        if (!$layout || !$user) {
            return;
        }

        $filePath = Storage::disk('local')->path($this->relativePath);

        if (!file_exists($filePath)) {
            Log::error("Job ImportarLancamentoContabilJob: Arquivo não encontrado em {$filePath}");
            return;
        }

        try {
            $fileReader = (new OptimizedExcelImport($layout, $filePath));
            $excelData = $fileReader->getData();
            
            // Simulação de progresso se fosse necessário, mas prepareData processa tudo de uma vez.
            // Para Filament Actions em background com progresso real, o ideal seria iterar aqui.
            
            self::prepareData($excelData, $layout, $user);

        } catch (Exception $e) {
            Log::error("Erro no Job ImportarLancamentoContabilJob: " . $e->getMessage());
            throw $e;
        } finally {
            if (Storage::disk('local')->exists($this->relativePath)) {
                Storage::disk('local')->delete($this->relativePath);
            }
        }
    }
}
