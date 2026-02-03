<?php

namespace App\Jobs\DashboardFiscal;

use App\Models\StatisticIssuer;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AggregateMonthlyFiscalFinancialStatsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    public function __construct(
        protected int $tenantId,
        protected string $monthKey,
        protected string $docTipo,
        protected string $tipo,
        protected ?string $issuerCnpj = null,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $monthStart = CarbonImmutable::createFromFormat('Y-m', $this->monthKey)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        [$table, $issuerColumn, $dateColumn, $statusColumn, $statusOk, $metricColumns] = $this->resolveSourceAndMetrics();

        $rows = $this->aggregate(
            table: $table,
            issuerColumn: $issuerColumn,
            dateColumn: $dateColumn,
            statusColumn: $statusColumn,
            statusOk: $statusOk,
            metricColumns: $metricColumns,
            monthStart: $monthStart,
            monthEnd: $monthEnd,
        );

        foreach ($rows as $row) {
            $issuer = (string) ($row->issuer ?? '');

            if ($issuer === '') {
                continue;
            }

            foreach ($metricColumns as $metric => $column) {
                $value = (float) ($row->{$metric} ?? 0);

                StatisticIssuer::query()->updateOrCreate(
                    [
                        'tenant_id' => $this->tenantId,
                        'issuer' => $issuer,
                        'periodo' => 'mensal',
                        'doc_tipo' => $this->docTipo,
                        'tipo' => $this->tipo,
                        'metrica' => $metric,
                        'data' => $this->monthKey,
                    ],
                    [
                        'data_ref' => $monthStart->toDateString(),
                        'valor' => $value,
                    ],
                );
            }
        }
    }

    private function resolveSourceAndMetrics(): array
    {
        return match ($this->docTipo) {
            'nfe' => $this->resolveNfe(),
            'cte' => $this->resolveCte(),
            'nfse' => $this->resolveNfse(),
            default => throw new \InvalidArgumentException("doc_tipo inválido: {$this->docTipo}"),
        };
    }

    private function resolveNfe(): array
    {
        $issuerColumn = match ($this->tipo) {
            'saida' => 'emitente_cnpj',
            'entrada' => 'destinatario_cnpj',
            default => throw new \InvalidArgumentException("tipo inválido para nfe: {$this->tipo}"),
        };

        return [
            'nfes',
            $issuerColumn,
            'data_emissao',
            'status_nota',
            100,
            [
                'valor_total' => 'vNfe',
                'icms' => 'vICMS',
                'icms_st' => 'vST',
                'ipi' => 'vIPI',
                'pis' => 'vPIS',
                'cofins' => 'vCOFINS',
            ],
        ];
    }

    private function resolveCte(): array
    {
        $issuerColumn = match ($this->tipo) {
            'saida' => 'emitente_cnpj',
            'entrada' => 'destinatario_cnpj',
            'tomador' => 'tomador_cnpj',
            default => throw new \InvalidArgumentException("tipo inválido para cte: {$this->tipo}"),
        };

        return [
            'ctes',
            $issuerColumn,
            'data_emissao',
            'status_cte',
            100,
            [
                'valor_total' => 'vCTe',
            ],
        ];
    }

    private function resolveNfse(): array
    {
        if ($this->tipo !== 'tomador' && $this->tipo !== 'saida') {
            throw new \InvalidArgumentException("tipo inválido para nfse: {$this->tipo}");
        }

        return [
            'nfses',
            $this->tipo === 'saida' ? 'prestador_cnpj' : 'tomador_cnpj',
            'data_emissao',
            null,
            null,
            [
                'valor_total' => 'valor_servico',
            ],
        ];
    }

    private function aggregate(
        string $table,
        string $issuerColumn,
        string $dateColumn,
        ?string $statusColumn,
        int|string|null $statusOk,
        array $metricColumns,
        CarbonImmutable $monthStart,
        CarbonImmutable $monthEnd,
    ): array {
        $issuers = DB::table('issuers')
            ->where('issuers.tenant_id', $this->tenantId)
            ->whereNotNull('issuers.cnpj');

        if ($this->issuerCnpj !== null) {
            $issuers->where('issuers.cnpj', $this->issuerCnpj);
        }

        $issuers->leftJoin("{$table} as docs", function ($join) use ($issuerColumn, $monthStart, $monthEnd, $dateColumn, $table, $statusColumn, $statusOk) {
            $join->on("docs.{$issuerColumn}", '=', 'issuers.cnpj')
                ->where('docs.tenant_id', '=', $this->tenantId)
                ->whereBetween("docs.{$dateColumn}", [$monthStart, $monthEnd]);

            if ($statusColumn !== null) {
                $join->where("docs.{$statusColumn}", '=', $statusOk);
            }

            if ($table === 'nfses') {
                $join->where(function ($query) {
                    $query->whereNull('docs.cancelada')->orWhere('docs.cancelada', false);
                });
            }
        });

        $selects = ['issuers.cnpj as issuer'];
        foreach ($metricColumns as $metric => $column) {
            $selects[] = "COALESCE(SUM(docs.{$column}), 0) as {$metric}";
        }

        return $issuers
            ->selectRaw(implode(', ', $selects))
            ->groupBy('issuers.cnpj')
            ->get()
            ->all();
    }
}
