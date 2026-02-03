<?php

namespace App\Filament\Widgets;

use App\Models\StatisticIssuer;
use App\Models\NotaFiscalEletronica;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticData
{
    /**
     * Retorna série mensal a partir do cache persistente em `statistic_issuers`.
     *
     * @param  int  $tenantId
     * @param  string  $issuerCnpj
     * @param  array<int, string>  $monthKeys  Lista de meses no formato `YYYY-MM`.
     * @param  string  $docTipo  Ex.: nfe, cte, nfse.
     * @param  string  $tipo  Ex.: entrada, saida, tomador.
     * @param  string  $metrica  Ex.: qtd, valor_total, icms, ipi, pis, cofins, icms_st.
     * @return array<string, float> Map `YYYY-MM` => valor.
     */
    public static function getMonthlySeriesFromCache(
        int $tenantId,
        string $issuerCnpj,
        array $monthKeys,
        string $docTipo,
        string $tipo,
        string $metrica,
    ): array {
        $monthKeys = array_values(array_unique(array_filter($monthKeys)));
        sort($monthKeys);

        if ($monthKeys === []) {
            return [];
        }

        $from = $monthKeys[0];
        $to = $monthKeys[count($monthKeys) - 1];

        $cacheKey = "dashboard_fiscal:financial_series:tenant={$tenantId}:issuer={$issuerCnpj}:doc={$docTipo}:tipo={$tipo}:metrica={$metrica}:from={$from}:to={$to}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($tenantId, $issuerCnpj, $monthKeys, $docTipo, $tipo, $metrica) {
            $rows = StatisticIssuer::query()
                ->where('tenant_id', $tenantId)
                ->where('issuer', $issuerCnpj)
                ->where('periodo', 'mensal')
                ->where('doc_tipo', $docTipo)
                ->where('tipo', $tipo)
                ->where('metrica', $metrica)
                ->whereIn('data', $monthKeys)
                ->get(['data', 'valor']);

            $result = [];
            foreach ($monthKeys as $monthKey) {
                $result[$monthKey] = 0.0;
            }

            foreach ($rows as $row) {
                $monthKey = (string) $row->data;
                if (! array_key_exists($monthKey, $result)) {
                    continue;
                }

                $result[$monthKey] = self::normalizeMoney($row->valor);
            }

            return $result;
        });
    }

    /**
     * Série mensal com filtro de categoria fiscal sem persistência no banco.
     *
     * @param  int  $tenantId
     * @param  string  $issuerCnpj
     * @param  array<int, string>  $monthKeys  Lista de meses no formato `YYYY-MM`.
     * @param  string  $tipo  entrada|saida
     * @param  string  $categoria  Ex.: faturamento ou anexo:II.
     * @return array<string, float> Map `YYYY-MM` => valor.
     */
    public static function getMonthlyNfeCategorySeries(
        int $tenantId,
        string $issuerCnpj,
        array $monthKeys,
        string $tipo,
        string $categoria,
    ): array {
        $monthKeys = array_values(array_unique(array_filter($monthKeys)));
        sort($monthKeys);

        if ($monthKeys === []) {
            return [];
        }

        $from = $monthKeys[0];
        $to = $monthKeys[count($monthKeys) - 1];

        $cacheKey = "dashboard_fiscal:nfe_category_series:tenant={$tenantId}:issuer={$issuerCnpj}:tipo={$tipo}:categoria={$categoria}:from={$from}:to={$to}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tenantId, $issuerCnpj, $monthKeys, $tipo, $categoria) {
            $result = [];
            foreach ($monthKeys as $monthKey) {
                $result[$monthKey] = 0.0;
            }

            $start = Carbon::createFromFormat('Y-m', $monthKeys[0])->startOfMonth()->toDateString();
            $end = Carbon::createFromFormat('Y-m', $monthKeys[count($monthKeys) - 1])->endOfMonth()->toDateString();

            $issuerColumn = $tipo === 'entrada' ? 'destinatario_cnpj' : 'emitente_cnpj';

            $query = DB::table('nfes')
                ->join('nfe_products', 'nfes.id', '=', 'nfe_products.nfe_id')
                ->join('cfops', 'cfops.codigo', '=', 'nfe_products.cfop')
                ->where('nfes.tenant_id', $tenantId)
                ->where("nfes.{$issuerColumn}", $issuerCnpj)
                ->where('nfes.status_nota', 100)
                ->whereBetween('nfes.data_emissao', [$start, $end]);

            $categoria = trim(mb_strtolower($categoria));

            if ($categoria === 'faturamento') {
                $query->where('cfops.is_faturamento', true);
            } elseif (str_starts_with($categoria, 'anexo:')) {
                $anexo = trim(substr($categoria, strlen('anexo:')));
                $query->where('cfops.anexo', $anexo);
            }

            try {
                $rows = $query
                    ->selectRaw("DATE_FORMAT(nfes.data_emissao, '%Y-%m') as month_key, SUM(nfe_products.valor_total) as total")
                    ->groupBy(DB::raw("DATE_FORMAT(nfes.data_emissao, '%Y-%m')"))
                    ->pluck('total', 'month_key');

                foreach ($rows as $monthKey => $total) {
                    if (! array_key_exists($monthKey, $result)) {
                        continue;
                    }

                    $result[$monthKey] = self::normalizeMoney($total);
                }
            } catch (\Throwable $e) {
                Log::error('Falha ao consultar série NFe por categoria', [
                    'tenant_id' => $tenantId,
                    'issuer' => $issuerCnpj,
                    'tipo' => $tipo,
                    'categoria' => $categoria,
                    'error' => $e->getMessage(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Calcula e cacheia estatísticas financeiras (total, média, tendência, comparativos) a partir de uma série mensal.
     *
     * @param  array<string, float|int|string|null>  $series  Map `YYYY-MM` => valor.
     * @return array{total: float, media: float, tendencia: array{slope: float, direcao: string}, comparativos: array{mom: array{delta: float, pct: float}, yoy: array{delta: float, pct: float}}}
     */
    public static function computeFinancialStats(array $series): array
    {
        $normalized = self::normalizeSeries($series);
        $values = array_values($normalized);
        $keys = array_keys($normalized);

        $total = self::normalizeMoney(array_sum($values));
        $media = count($values) > 0 ? self::normalizeMoney($total / count($values)) : 0.0;

        $tendencia = self::computeTrend($values);
        $comparativos = self::computeComparisons($normalized, $keys);

        return [
            'total' => $total,
            'media' => $media,
            'tendencia' => $tendencia,
            'comparativos' => $comparativos,
        ];
    }

    /**
     * Normaliza valores monetários.
     */
    public static function normalizeMoney(mixed $value): float
    {
        if ($value === null) {
            return 0.0;
        }

        if (is_string($value)) {
            $value = trim($value);

            $hasDot = str_contains($value, '.');
            $hasComma = str_contains($value, ',');

            if ($hasDot && $hasComma) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } elseif ($hasComma) {
                $value = str_replace(',', '.', $value);
            }
        }

        if (! is_numeric($value)) {
            return 0.0;
        }

        $float = (float) $value;

        if (! is_finite($float)) {
            return 0.0;
        }

        return round($float, 4);
    }

    /**
     * @param  array<string, float|int|string|null>  $series
     * @return array<string, float>
     */
    private static function normalizeSeries(array $series): array
    {
        ksort($series);
        $normalized = [];

        foreach ($series as $key => $value) {
            $normalized[(string) $key] = self::normalizeMoney($value);
        }

        return $normalized;
    }

    /**
     * @param  array<int, float>  $values
     * @return array{slope: float, direcao: string}
     */
    private static function computeTrend(array $values): array
    {
        $n = count($values);

        if ($n < 2) {
            return ['slope' => 0.0, 'direcao' => 'estavel'];
        }

        $sumX = 0.0;
        $sumY = 0.0;
        $sumXY = 0.0;
        $sumXX = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $x = (float) $i;
            $y = (float) $values[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }

        $den = ($n * $sumXX) - ($sumX * $sumX);
        $slope = $den == 0.0 ? 0.0 : (($n * $sumXY) - ($sumX * $sumY)) / $den;
        $slope = self::normalizeMoney($slope);

        $direcao = $slope > 0 ? 'alta' : ($slope < 0 ? 'baixa' : 'estavel');

        return ['slope' => $slope, 'direcao' => $direcao];
    }

    /**
     * @param  array<string, float>  $series
     * @param  array<int, string>  $keys
     * @return array{mom: array{delta: float, pct: float}, yoy: array{delta: float, pct: float}}
     */
    private static function computeComparisons(array $series, array $keys): array
    {
        $lastKey = $keys[count($keys) - 1] ?? null;

        if ($lastKey === null) {
            return [
                'mom' => ['delta' => 0.0, 'pct' => 0.0],
                'yoy' => ['delta' => 0.0, 'pct' => 0.0],
            ];
        }

        $lastValue = $series[$lastKey] ?? 0.0;

        $prevKey = $keys[count($keys) - 2] ?? null;
        $prevValue = $prevKey !== null ? ($series[$prevKey] ?? 0.0) : 0.0;

        $momDelta = self::normalizeMoney($lastValue - $prevValue);
        $momPct = $prevValue == 0.0 ? 0.0 : self::normalizeMoney(($momDelta / $prevValue) * 100);

        $yoyKey = self::previousYearMonthKey($lastKey);
        $yoyBase = $yoyKey !== null ? ($series[$yoyKey] ?? 0.0) : 0.0;
        $yoyDelta = self::normalizeMoney($lastValue - $yoyBase);
        $yoyPct = $yoyBase == 0.0 ? 0.0 : self::normalizeMoney(($yoyDelta / $yoyBase) * 100);

        return [
            'mom' => ['delta' => $momDelta, 'pct' => $momPct],
            'yoy' => ['delta' => $yoyDelta, 'pct' => $yoyPct],
        ];
    }

    private static function previousYearMonthKey(string $monthKey): ?string
    {
        try {
            $date = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth()->subYear();

            return $date->format('Y-m');
        } catch (\Throwable $e) {
            Log::warning('Chave de mês inválida para YoY', ['monthKey' => $monthKey, 'error' => $e->getMessage()]);

            return null;
        }
    }

    public static function entradaVsImpostoMensal($issuer)
    {
        $faturamentoData = [];
        $faturamentoNfseData = [];
        $cteEntradaData = [];
        $cteSaidaData = [];
        $icmsData = [];
        $icmsSTData = [];
        $ipiData = [];
        $cofinsData = [];
        $pisData = [];
        $months = [];

        $min_config = config('admin.min_months_to_display');
        $period = now()->subMonths($min_config)->monthsUntil(now());

        foreach ($period as $date) {
            $index = $date->format('m-Y');
            $months[$index] = [];
            $faturamentoData[$index] = 0;
            $faturamentoNfseData[$index] = 0;
            $cteEntradaData[$index] = 0;
            $cteSaidaData[$index] = 0;
            $icmsData[$index] = 0;
            $icmsSTData[$index] = 0;
            $ipiData[$index] = 0;
            $cofinsData[$index] = 0;
            $pisData[$index] = 0;
        }

        $date = Carbon::today()->subMonth($min_config);

        $faturamento = (new self)->getSaidaMensalNfe($issuer);

        $faturamentoNfse = (new self)->getSaidaNFSEMensal($issuer);

        $cteEntrada = (new self)->getCteEntradaMensal($issuer);

        $cteSaida = (new self)->getCteSaidaMensal($issuer);

        $icms = (new self)->getICMSSaidaMensal($issuer);

        $icmsST = (new self)->getICMSSTSaidaMensal($issuer);

        $ipi = (new self)->getIPISaidaMensal($issuer);

        $pis = (new self)->getPISSaidaMensal($issuer);

        $cofins = (new self)->getCOFINSSaidaMensal($issuer);

        foreach ($faturamento as $row) {

            $faturamentoData[$row->date] = $row->price;
        }

        foreach ($faturamentoNfse as $row) {

            $faturamentoNfseData[$row->date] = $row->price;
        }

        foreach ($cteEntrada as $row) {

            $cteEntradaData[$row->date] = $row->price;
        }

        foreach ($cteSaida as $row) {

            $cteSaidaData[$row->date] = $row->price;
        }

        foreach ($icms as $row) {

            $icmsData[$row->date] = $row->price;
        }

        foreach ($icmsST as $row) {

            $icmsSTData[$row->date] = $row->price;
        }

        foreach ($ipi as $row) {

            $ipiData[$row->date] = $row->price;
        }

        foreach ($pis as $row) {

            $pisData[$row->date] = $row->price;
        }

        foreach ($cofins as $row) {

            $cofinsData[$row->date] = $row->price;
        }

        $months = array_reverse($months);

        array_walk($months, function (&$value, $key) use (
            $faturamentoData,
            $faturamentoNfseData,
            $cteEntradaData,
            $cteSaidaData,
            $icmsData,
            $icmsSTData,
            $ipiData,
            $cofinsData,
            $pisData
        ) {
            $value['faturamento'] = $faturamentoData[$key];
            $value['faturamento-nfse'] = $faturamentoNfseData[$key];
            $value['cteEntrada'] = $cteEntradaData[$key];
            $value['cteSaida'] = $cteSaidaData[$key];
            $value['icms'] = $icmsData[$key];
            $value['icmsST'] = $icmsSTData[$key];
            $value['ipi'] = $ipiData[$key];
            $value['pis'] = $pisData[$key];
            $value['cofins'] = $cofinsData[$key];
            $value['cprb'] = $faturamentoData[$key] * 0.015;
            $value['csll'] = $faturamentoData[$key] * 0.0108;
            $value['irpj'] = $faturamentoData[$key] * 0.0102;
            $value['faturamentoLiquido'] = $value['faturamento'] - $value['faturamento-nfse'] - $value['icms'] - $value['icmsST'] - $value['ipi'] - $value['pis'] - $value['cofins'] - $value['cprb'] - $value['csll'] - $value['irpj'];
        });

        return $months;
    }

    public static function saidaVsImpostoMensal($issuer)
    {
        $saidaData = [];
        $cteEntradaData = [];
        $cteSaidaData = [];
        $icmsData = [];
        $ipiData = [];
        $cofinsData = [];
        $pisData = [];
        $months = [];

        $min_config = config('admin.min_months_to_display');
        $period = now()->subMonths($min_config)->monthsUntil(now());

        foreach ($period as $date) {
            $index = $date->format('m-Y');
            $months[$index] = [];
            $saidaData[$index] = 0;
            $cteEntradaData[$index] = 0;
            $cteSaidaData[$index] = 0;
            $icmsData[$index] = 0;
            $ipiData[$index] = 0;
            $cofinsData[$index] = 0;
            $pisData[$index] = 0;
        }

        $date = Carbon::today()->subMonth($min_config);

        $saida = (new self)->getSaidaMensalNfe($issuer);

        $cteEntrada = (new self)->getCteEntradaMensal($issuer);

        $cteSaida = (new self)->getCteSaidaMensal($issuer);

        $icms = (new self)->getICMSEntradaMensal($issuer);

        $ipi = (new self)->getIPIEntradaMensal($issuer);

        $pis = (new self)->getPISEntradaMensal($issuer);

        $cofins = (new self)->getCOFINSEntradaMensal($issuer);

        foreach ($saida as $row) {

            $saidaData[$row->date] = $row->price;
        }

        foreach ($cteEntrada as $row) {

            $cteEntradaData[$row->date] = $row->price;
        }

        foreach ($cteSaida as $row) {

            $cteSaidaData[$row->date] = $row->price;
        }

        foreach ($icms as $row) {

            $icmsData[$row->date] = $row->price;
        }

        foreach ($ipi as $row) {

            $ipiData[$row->date] = $row->price;
        }

        foreach ($pis as $row) {

            $pisData[$row->date] = $row->price;
        }

        foreach ($cofins as $row) {

            $cofinsData[$row->date] = $row->price;
        }

        $months = array_reverse($months);

        array_walk($months, function (&$value, $key) use ($saidaData, $cteEntradaData, $cteSaidaData, $icmsData, $ipiData, $cofinsData, $pisData) {
            $value['compra'] = $saidaData[$key];
            $value['cteEntrada'] = $cteEntradaData[$key];
            $value['cteSaida'] = $cteSaidaData[$key];
            $value['icms'] = $icmsData[$key];
            $value['ipi'] = $ipiData[$key];
            $value['pis'] = $pisData[$key];
            $value['cofins'] = $cofinsData[$key];
            $value['compra-liquida'] = $value['compra'] + $value['cteEntrada'] + $value['cteSaida'] - $value['icms'] - $value['ipi'] - $value['pis'] - $value['cofins'];
        });

        return $months;
    }

    public static function faturamentoMensal($issuer)
    {
        $compraData = [];
        $faturamentoData = [];
        $months = [];

        $min_config = config('admin.min_months_to_display');
        $period = now()->subMonths($min_config)->monthsUntil(now());

        foreach ($period as $date) {
            $index = $date->format('m-Y');
            $months[$index] = ['expense' => 0, 'income' => 0];
            $compraData[$index] = 0;
            $faturamentoData[$index] = 0;
        }

        $date = Carbon::today()->subMonth($min_config);

        $compra = (new self)->getEntradaMensalNfe($issuer);

        $faturamento = (new self)->getSaidaMensalNfe($issuer);

        foreach ($faturamento as $row) {

            $faturamentoData[$row->date] = $row->price;
        }

        foreach ($compra as $row) {

            $compraData[$row->date] = $row->price;
        }

        $months = array_reverse($months);

        array_walk($months, function (&$value, $key) use ($faturamentoData, $compraData) {
            $value['income'] = $faturamentoData[$key];
            $value['expense'] = $compraData[$key];
        });

        return $months;
    }



    public static function produtosMaisVendidos($issuer, $activeFilter)
    {
        $values = [];

        // $totalSeller = NotaFiscalEletronica::leftJoin('nfe_products', 'nfes.id', '=', 'nfe_products.nfe_id')
        //     ->where('emitente_cnpj', $issuer->cnpj)
        //     ->where('data_emissao', '>=', $activeFilter)
        //     ->sum('nfe_products.valor_total');

        $productSeller = NotaFiscalEletronica::leftJoin('nfe_products', 'nfes.id', '=', 'nfe_products.nfe_id')
            ->select(
                DB::raw('(sum(nfe_products.valor_total)) as total'),
                DB::raw('(sum(nfe_products.quantidade)) as amount'),
                'codigo_produto',
                'descricao_produto',

            )

            ->where('emitente_cnpj', $issuer->cnpj)
            ->where('status_nota', 100)
            ->where('data_emissao', '>=', $activeFilter)
            ->orderBy('total', 'desc')
            ->groupBy('nfe_products.codigo_produto')
            ->groupBy('nfe_products.quantidade')
            ->groupBy('nfe_products.descricao_produto')
            ->limit(15)
            ->get();

        foreach ($productSeller as $row) {

            $values[] = [
                // 'total' => 'R$ ' . number_format($row->total, 2, ',', '.') . ' (' . number_format($row->total / $totalSeller * 100, 2, ',', '.') . '%)',
                'total' => $row->total,
                'label' => $row->descricao_produto,
                'amount' => $row->amount,
            ];
        }

        return $values;
    }

    public static function topClientes($issuer, $activeFilter)
    {

        $topClient = NotaFiscalEletronica::select(
            DB::raw('(sum(vNfe)) as total'),
            'destinatario_cnpj',
            'destinatario_razao_social'
        )
            ->where('emitente_cnpj', $issuer->cnpj)
            ->where('data_emissao', '>=', $activeFilter)
            ->where('status_nota', 100)
            ->orderBy('total', 'desc')
            ->groupBy('destinatario_cnpj')
            ->groupBy('destinatario_razao_social')
            ->limit(15)
            ->get();


        return $topClient;
    }

    public static function topFornecedores($issuer, $activeFilter)
    {

        $topFornecedores = NotaFiscalEletronica::select(
            DB::raw('(sum(vNfe)) as total'),
            'emitente_cnpj',
            'emitente_razao_social'
        )
            ->where('destinatario_cnpj', $issuer->cnpj)
            ->where('data_emissao', '>=', $activeFilter)
            ->where('status_nota', 100)
            ->orderBy('total', 'desc')
            ->groupBy('emitente_cnpj')
            ->groupBy('emitente_razao_social')
            ->limit(15)
            ->get();


        return $topFornecedores;
    }

    /**
     * @return array<int, string>
     */
    private function getMonthKeysForDisplay(int $minMonthsToDisplay): array
    {
        $start = now()->toImmutable()->subMonths($minMonthsToDisplay)->startOfMonth();
        $end = now()->toImmutable()->startOfMonth();

        $months = [];
        for ($cursor = $start; $cursor->lessThanOrEqualTo($end); $cursor = $cursor->addMonth()) {
            $months[] = $cursor->format('Y-m');
        }

        return $months;
    }

    /**
     * @param  array<int, string>  $monthKeys
     * @return array<int, object>
     */
    private function toLegacyMonthlyRows(
        int $tenantId,
        string $issuerCnpj,
        array $monthKeys,
        string $docTipo,
        string $tipo,
        string $metrica,
    ): array {
        try {
            $series = self::getMonthlySeriesFromCache(
                tenantId: $tenantId,
                issuerCnpj: $issuerCnpj,
                monthKeys: $monthKeys,
                docTipo: $docTipo,
                tipo: $tipo,
                metrica: $metrica,
            );

            $rows = [];
            foreach ($series as $monthKey => $value) {
                $rows[] = (object) [
                    'price' => self::normalizeMoney($value),
                    'date' => Carbon::createFromFormat('Y-m', $monthKey)->format('m-Y'),
                ];
            }

            return $rows;
        } catch (\Throwable $e) {
            Log::error('Falha ao montar série mensal a partir do cache', [
                'tenant_id' => $tenantId,
                'issuer' => $issuerCnpj,
                'doc_tipo' => $docTipo,
                'tipo' => $tipo,
                'metrica' => $metrica,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function getEntradaMensalNfe($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'entrada',
            metrica: 'valor_total',
        );
    }



    public function getSaidaMensalNfe($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'saida',
            metrica: 'valor_total',
        );
    }


    public function getCteEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'cte',
            tipo: 'tomador',
            metrica: 'valor_total',
        );
    }

    public function getCteSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'cte',
            tipo: 'saida',
            metrica: 'valor_total',
        );
    }

    public function getICMSSTSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'saida',
            metrica: 'icms_st',
        );
    }

    public function getICMSEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'entrada',
            metrica: 'icms',
        );
    }

    public function getICMSSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'saida',
            metrica: 'icms',
        );
    }

    public function getIPIEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'entrada',
            metrica: 'ipi',
        );
    }

    public function getIPISaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'saida',
            metrica: 'ipi',
        );
    }

    public function getPISEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'entrada',
            metrica: 'pis',
        );
    }

    public function getPISSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'saida',
            metrica: 'pis',
        );
    }

    public function getCOFINSEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'entrada',
            metrica: 'cofins',
        );
    }

    public function getCOFINSSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfe',
            tipo: 'saida',
            metrica: 'cofins',
        );
    }

    public function getSaidaNFSEMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $monthKeys = $this->getMonthKeysForDisplay($min_config);

        return $this->toLegacyMonthlyRows(
            tenantId: (int) $issuer->tenant_id,
            issuerCnpj: (string) $issuer->cnpj,
            monthKeys: $monthKeys,
            docTipo: 'nfse',
            tipo: 'saida',
            metrica: 'valor_total',
        );
    }
}
