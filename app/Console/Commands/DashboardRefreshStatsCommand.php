<?php

namespace App\Console\Commands;

use App\Models\Issuer;
use Illuminate\Console\Command;
use App\Services\DashboardFiscal\FiscalDashboardEtlService;

class DashboardRefreshStatsCommand extends Command
{
    protected $signature = 'dashboard:refresh-stats {--issuer=} {--from=} {--to=}';

    protected $description = 'Recalcula estatísticas mensais (cache) do dashboard fiscal em batch';

    public function handle()
    {
        $issuer = $this->option('issuer');
        $from = $this->option('from');
        $to = $this->option('to');


        $issuerCnpj = is_string($issuer) && $issuer !== '' ? $issuer : null;
        $fromMonth = is_string($from) && $from !== '' ? $from : now()->toImmutable()->startOfMonth()->format('Y-m');
        $toMonth = is_string($to) && $to !== '' ? $to : now()->toImmutable()->startOfMonth()->format('Y-m');

        $issuers = Issuer::with('tenant')
            ->where('is_enabled', true)
            ->when($issuerCnpj !== null, fn($q) => $q->where('cnpj', $issuerCnpj))
            ->get();

        foreach ($issuers as $issuer) {
            // Dispatch the batch job
            app(FiscalDashboardEtlService::class)
                ->dispatchMonthlyRefresh(
                    tenantId: $issuer->tenant_id,
                    issuerCnpj: $issuer->cnpj,
                    fromMonth: $fromMonth,
                    toMonth: $toMonth,
                );
        }

        return self::SUCCESS;
    }
}
