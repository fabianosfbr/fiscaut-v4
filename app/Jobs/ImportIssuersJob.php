<?php

namespace App\Jobs;

use App\Models\Issuer;
use App\Models\JobProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportIssuersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $jobProgressId,
        public ?string $tenantId = null
    ) {}

    public function handle(): void
    {
        $jobProgress = JobProgress::find($this->jobProgressId);

        if (! $jobProgress) {
            Log::error('JobProgress não encontrado: ' . $this->jobProgressId);
            return;
        }


        try {
            $jobProgress->update([
                'status' => 'processing',
                'progress' => 2,
                'message' => 'Lendo arquivo Excel...',
            ]);

            $collection = (new FastExcel)->import($this->filePath);
            $totalRows = $collection->count();
            $data = $collection->toArray();

            $jobProgress->update([
                'message' => 'Processando ' . $totalRows . ' registros...',
            ]);

            $processed = 0;


            foreach ($data as $row) {
                $razaoSocial = $row['razao_social'] ?? null;
                $cnpj = $row['cnpj'] ?? null;
                $users = $row['users'] ?? null;

                if (empty($razaoSocial) || empty($cnpj) || empty($users)) {
                    $processed++;
                    continue;
                }

                // Normaliza CNPJ: remove não-dígitos
                $cnpjNormalizado = preg_replace('/\D/', '', $cnpj);

                // Busca ou cria o issuer com tenant_id
                $issuer = Issuer::updateOrCreate(
                    [
                        'cnpj' => $cnpjNormalizado,
                        'tenant_id' => $this->tenantId,
                    ],
                    [
                        'razao_social' => $razaoSocial,
                    ]
                );

                if ($issuer) {
                    $userIds = array_map('trim', explode(';', $users));
                    $usersModel = \App\Models\User::whereIn('id', $userIds)->get();
                    if ($usersModel->isNotEmpty()) {
                        $issuer->users()->sync($usersModel->pluck('id')->toArray());
                    }
                }

                $processed++;

                // Atualiza progresso a cada 10 registros
                if ($processed % 10 === 0 || $processed === $totalRows) {
                    $progress = (int) round(($processed / $totalRows) * 100);
                    $jobProgress->update([
                        'progress' => $progress,
                        'message' => "Processando {$processed} de {$totalRows} registros...",
                    ]);
                }
                
            }

            $jobProgress->update([
                'status' => 'completed',
                'progress' => 100,
                'message' => "Importação concluída.",
            ]);

            // Remove arquivo temporário
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        } catch (\Exception $e) {
            Log::error('Erro na importação de issuers: ' . $e->getMessage(), [
                'file' => $this->filePath,
                'job_progress_id' => $this->jobProgressId,
                'trace' => $e->getTraceAsString(),
            ]);

            $jobProgress->update([
                'status' => 'failed',
                'message' => 'Erro: ' . $e->getMessage(),
            ]);
        }
    }
}
