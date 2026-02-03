<?php

namespace App\Services\DashboardFiscal;

use App\Jobs\DashboardFiscal\AggregateMonthlyFiscalStatsJob;
use App\Models\User;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Filament\Notifications\Notification;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class FiscalDashboardEtlService
{
    public function dispatchMonthlyRefresh(
        ?int $tenantId = null,
        ?string $issuerCnpj = null,
        ?string $fromMonth = null,
        ?string $toMonth = null,
    ): array {
        $months = $this->resolveMonthKeys($fromMonth, $toMonth);

        $batchIds = [];

        $jobs = [];
        $jobs = array_merge($jobs, $this->buildTenantJobs((int) $tenantId, $months, $issuerCnpj));

        $batch = Bus::batch($jobs)
            ->name("dashboard-fiscal:tenant={$tenantId}:{$months[0]}-{$months[count($months) - 1]}")
            ->allowFailures()
            ->then(function (Batch $batch) use ($tenantId, $months): void {
                Log::info('Dashboard fiscal atualizado', [
                    'tenant_id' => $tenantId,
                    'from' => $months[0],
                    'to' => $months[count($months) - 1],
                    'batch_id' => $batch->id,
                ]);
            })
            ->catch(function (Batch $batch, Throwable $exception) use ($tenantId, $months): void {
                Log::error('Falha ao atualizar dashboard fiscal', [
                    'tenant_id' => $tenantId,
                    'from' => $months[0],
                    'to' => $months[count($months) - 1],
                    'batch_id' => $batch->id,
                    'exception' => $exception->getMessage(),
                ]);
            })
            ->dispatch();
        
        $batchIds[] = $batch->id;

        return $batchIds;
    }

    private function buildTenantJobs(int $tenantId, array $months, ?string $issuerCnpj): array
    {
        $metrics = [
            ['doc_tipo' => 'nfe', 'tipo' => 'saida'],
            ['doc_tipo' => 'nfe', 'tipo' => 'entrada'],
            ['doc_tipo' => 'cte', 'tipo' => 'saida'],
            ['doc_tipo' => 'cte', 'tipo' => 'entrada'],
            ['doc_tipo' => 'cte', 'tipo' => 'tomador'],
            ['doc_tipo' => 'nfse', 'tipo' => 'tomador'],
        ];

        $jobs = [];

        foreach ($months as $monthKey) {
            foreach ($metrics as $metric) {
                $jobs[] = (new AggregateMonthlyFiscalStatsJob(
                    tenantId: $tenantId,
                    monthKey: $monthKey,
                    docTipo: $metric['doc_tipo'],
                    tipo: $metric['tipo'],
                    issuerCnpj: $issuerCnpj,
                ))->onQueue('low');

                //ds('Job: ' . $monthKey . ' ' . $metric['doc_tipo'] . ' ' . $metric['tipo']);
            }
        }



        return $jobs;
    }

    private function resolveMonthKeys(?string $fromMonth, ?string $toMonth): array
    {
        $to = $toMonth !== null
            ? CarbonImmutable::createFromFormat('Y-m', $toMonth)->startOfMonth()
            : now()->toImmutable()->startOfMonth();

        $from = $fromMonth !== null
            ? CarbonImmutable::createFromFormat('Y-m', $fromMonth)->startOfMonth()
            : $to->subMonth();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $months = [];
        for ($cursor = $from; $cursor->lessThanOrEqualTo($to); $cursor = $cursor->addMonth()) {
            $months[] = $cursor->format('Y-m');
        }

        return $months;
    }
}
