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

class AggregateMonthlyFiscalStatsJob implements ShouldQueue
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
    ) {
        $this->onQueue('low');
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $monthStart = CarbonImmutable::createFromFormat('Y-m', $this->monthKey)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        [$table, $issuerColumn, $dateColumn] = $this->resolveSource();

        $totals = $this->aggregateCounts(
            table: $table,
            issuerColumn: $issuerColumn,
            monthStart: $monthStart,
            monthEnd: $monthEnd,
            dateColumn: $dateColumn,
        );

        foreach ($totals as $issuer => $count) {
            StatisticIssuer::query()->updateOrCreate(
                [
                    'tenant_id' => $this->tenantId,
                    'issuer' => $issuer,
                    'periodo' => 'mensal',
                    'doc_tipo' => $this->docTipo,
                    'tipo' => $this->tipo,
                    'metrica' => 'qtd',
                    'data' => $this->monthKey,
                ],
                [
                    'data_ref' => $monthStart->toDateString(),
                    'valor' => $count,
                ],
            );
        }
    }

    private function resolveSource(): array
    {
        return match ($this->docTipo) {
            'nfe' => $this->resolveNfeSource(),
            'cte' => $this->resolveCteSource(),
            'nfse' => $this->resolveNfseSource(),
            default => throw new \InvalidArgumentException("doc_tipo inválido: {$this->docTipo}"),
        };
    }

    private function resolveNfeSource(): array
    {
        return match ($this->tipo) {
            'saida' => ['nfes', 'emitente_cnpj', 'data_emissao'],
            'entrada' => ['nfes', 'destinatario_cnpj', 'data_emissao'],
            'tomador' => ['ctes', 'tomador_cnpj', 'data_emissao'],
            default => throw new \InvalidArgumentException("tipo inválido para nfe: {$this->tipo}"),
        };
    }

    private function resolveCteSource(): array
    {
        return match ($this->tipo) {
            'saida' => ['ctes', 'emitente_cnpj', 'data_emissao'],
            'entrada' => ['ctes', 'destinatario_cnpj', 'data_emissao'],
            'tomador' => ['ctes', 'tomador_cnpj', 'data_emissao'],
            default => throw new \InvalidArgumentException("tipo inválido para cte: {$this->tipo}"),
        };
    }

    private function resolveNfseSource(): array
    {
        return match ($this->tipo) {
            'tomador' => ['nfses', 'tomador_cnpj', 'data_emissao'],
            'saida' => ['nfses', 'prestador_cnpj', 'data_emissao'],
            default => throw new \InvalidArgumentException("tipo inválido para nfse: {$this->tipo}"),
        };
    }

    private function aggregateCounts(
        string $table,
        string $issuerColumn,
        CarbonImmutable $monthStart,
        CarbonImmutable $monthEnd,
        string $dateColumn,
    ): array {
        $issuers = DB::table('issuers')
            ->where('issuers.tenant_id', $this->tenantId)
            ->whereNotNull('issuers.cnpj');

        if ($this->issuerCnpj !== null) {
            $issuers->where('issuers.cnpj', $this->issuerCnpj);
        }

        $issuers->leftJoin("{$table} as docs", function ($join) use ($issuerColumn, $monthStart, $monthEnd, $dateColumn, $table) {
            $join->on("docs.{$issuerColumn}", '=', 'issuers.cnpj')
                ->whereBetween("docs.{$dateColumn}", [$monthStart, $monthEnd]);

            if ($table === 'nfses') {
                $join->where(function ($query) {
                    $query->whereNull('docs.cancelada')->orWhere('docs.cancelada', false);
                });
            }
        });

        $totals = $issuers
            ->selectRaw('issuers.cnpj as issuer, count(docs.id) as total')
            ->groupBy('issuers.cnpj')
            ->pluck('total', 'issuer');

        return $totals->map(fn ($value) => (int) $value)->all();
    }
}
