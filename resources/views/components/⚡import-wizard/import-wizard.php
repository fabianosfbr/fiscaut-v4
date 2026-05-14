<?php

use App\Jobs\ImportIssuersJob;
use App\Models\JobProgress;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new class extends Component
{
    use WithFileUploads;

    #[Validate(['uploadedFile' => 'required|file|mimes:xlsx,xls'])]
    public $uploadedFile;

    public array $validationResults = [];

    public string $status = 'idle';

    public int $step = 1;

    public array $fileValidation = [];

    // Step 3: Import progress tracking
    public ?string $jobProgressId = null;

    public int $importProgress = 0;

    public string $importMessage = 'Aguardando...';

    public string $importStatus = 'idle';

    public int $importCreated = 0;

    public int $importUpdated = 0;

    // Polling interval in milliseconds (2 seconds)
    protected int $pollInterval = 2000;

    public function mount(): void {}

    public function previousStep()
    {

        if ($this->step > 1) {
            $this->step--;
        }

    }

    public function setStep(int $step)
    {

        if ($step >= 1 && $step <= 3) {
            $this->step = $step;
        }

    }

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validateFile();

            if (! empty($this->fileValidation['errors'])) {
                return;
            }
        }

        if ($this->step === 2) {
            $this->startImport();
        }

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function startImport(): void
    {
        $normalizedPath = $this->getFilePath($this->uploadedFile);

        if (empty($normalizedPath) || ! file_exists($normalizedPath)) {
            return;
        }

        // Create JobProgress record
        $jobProgress = JobProgress::create([
            'status' => 'pending',
            'progress' => 0,
            'message' => 'Aguardando início do processamento...',
        ]);

        $this->jobProgressId = $jobProgress->id;
        $this->importStatus = 'completed';
        $this->importMessage = 'Iniciando importação...';
        $this->importProgress = 0;

        // Get current user's tenant_id
        $tenantId = Auth::user()?->tenant_id;

        // Dispatch the import job
        ImportIssuersJob::dispatch($normalizedPath, $jobProgress->id, $tenantId)->onQueue('low');

        $this->importStatus = 'processing';
    }

    #[Computed]
    public function jobProgress(): ?JobProgress
    {
        if (empty($this->jobProgressId)) {
            return null;
        }

        return JobProgress::find($this->jobProgressId);
    }

    public function refreshProgress(): void
    {

        $jobProgress = $this->jobProgress;

        if (! $jobProgress) {
            return;
        }

        $this->importProgress = $jobProgress->progress;
        $this->importMessage = $jobProgress->message;
        $this->importStatus = $jobProgress->status;

        ds($this->importProgress);
    }

    public function getFilePath(mixed $file): ?string
    {
        if (empty($file)) {
            return null;
        }

        // Se for um objeto UploadedFile, retorna o caminho temporário
        if ($file instanceof UploadedFile) {
            return $file->getPathname();
        }

        // Se for string, processa normalmente
        if (is_string($file)) {
            return ltrim($file, '/');
        }

        return null;
    }

    public function validateFile(): void
    {
        $this->fileValidation = [];

        $normalizedPath = $this->getFilePath($this->uploadedFile);

        if (empty($normalizedPath) || ! file_exists($normalizedPath)) {
            $this->fileValidation = ['errors' => ['Arquivo não encontrado.']];

            return;
        }

        try {
            $headers = (new FastExcel)->import($normalizedPath, function ($row) {
                return array_keys($row);
            }, 1)->first();

            $requiredColumns = ['razao_social', 'cnpj', 'users'];
            $missingColumns = [];
            $normalizedHeaders = array_map(fn ($h) => strtolower(trim($h)), $headers);

            foreach ($requiredColumns as $column) {
                if (! in_array($column, $normalizedHeaders)) {
                    $missingColumns[] = $column;
                }
            }

            if (! empty($missingColumns)) {
                $this->fileValidation = [
                    'errors' => ['Colunas obrigatórias ausentes: '.implode(', ', $missingColumns)],
                ];

                return;
            }

            $allData = (new FastExcel)->import($normalizedPath);
            $totalRows = $allData->count();
            $previewRows = $allData->take(5)->toArray();

            $this->fileValidation = [
                'success' => true,
                'total_rows' => $totalRows,
                'preview' => $previewRows,
                'headers' => $headers,
            ];
        } catch (Exception $e) {
            $this->fileValidation = ['errors' => ['Erro ao ler arquivo: '.$e->getMessage()]];
        }
    }
};
