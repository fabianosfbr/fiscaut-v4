<?php

namespace App\Filament\App\Pages\Relatorio;

use App\Models\NotaFiscalEletronica;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticData
{
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

    public static function faturamentoDiario($issuer)
    {
        $compraData = [];
        $faturamentoData = [];
        $days = [];

        $min_config = config('admin.min_days_to_display');
        $date = now()->subDays($min_config);

        for ($i = $date; $i <= Carbon::today(); $i->modify('+1 day')) {
            $date = $i->format('d-m-Y');
            $days[$i->format('d-m-Y')] = ['expense' => 0, 'income' => 0];
            $compraData[$i->format('d-m-Y')] = 0;
            $faturamentoData[$i->format('d-m-Y')] = 0;
        }

        $date = now()->subDays($min_config)->format('d-m-Y');

        $compra = (new self)->getEntradaDiariaNfe($issuer);

        $faturamento = (new self)->getSaidaDiariaNfe($issuer);

        foreach ($faturamento as $row) {

            $faturamentoData[$row->date] = $row->price;
        }

        foreach ($compra as $row) {

            $compraData[$row->date] = $row->price;
        }

        $days = array_reverse($days);

        array_walk($days, function (&$value, $key) use ($faturamentoData, $compraData) {
            $value['income'] = $faturamentoData[$key];
            $value['expense'] = $compraData[$key];
        });

        return $days;
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

    public function getEntradaMensalNfe($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = Cache::remember('entrada-mensal-nfe-' . $issuer->cnpj, 600, function () use ($issuer, $data_emissao) {
            return DB::select("SELECT SUM(nfes.vNfe) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.destinatario_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
                $issuer->cnpj,
                $data_emissao,
            ]);
        });

        return $values;
    }

    public function getEntradaDiariaNfe($issuer)
    {
        $min_config = config('admin.min_days_to_display');
        $data_emissao = now()->subDays($min_config)->format('Y-m-d 00:00:00');

        $values = Cache::remember('entrada-diaria-nfe-' . $issuer->cnpj, 600, function () use ($issuer, $data_emissao) {
            return DB::select("SELECT SUM(nfes.vNfe) AS price,  DATE_FORMAT(nfes.data_emissao, '%d-%m-%Y') AS date FROM nfes where nfes.destinatario_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%d-%m-%Y') ORDER BY 2 ASC", [
                $issuer->cnpj,
                $data_emissao,
            ]);
        });

        return $values;
    }

    public function getSaidaMensalNfe($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = Cache::remember('saida-mensal-nfe-' . $issuer->cnpj, 600, function () use ($issuer, $data_emissao) {
            return DB::select("SELECT SUM(nfes.vNfe) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.emitente_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
                $issuer->cnpj,
                $data_emissao,
            ]);
        });

        return $values;
    }

    public function getSaidaDiariaNfe($issuer)
    {
        $min_config = config('admin.min_days_to_display');

        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = Cache::remember('saida-diaria-nfe-' . $issuer->cnpj, 600, function () use ($issuer, $data_emissao) {
            return DB::select("SELECT SUM(nfes.vNfe) AS price,  DATE_FORMAT(nfes.data_emissao, '%d-%m-%Y') AS date FROM nfes where nfes.emitente_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%d-%m-%Y') ORDER BY 2 ASC", [
                $issuer->cnpj,
                $data_emissao,
            ]);
        });

        return $values;
    }

    public function getCteEntradaMensal($issuer)
    {

        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $return = DB::select("SELECT SUM(ctes.vCTe) AS price,  DATE_FORMAT(ctes.data_emissao, '%m-%Y') AS date FROM ctes where ctes.tomador_cnpj = ? and ctes.data_emissao >= ? and ctes.status_cte = 100 GROUP BY DATE_FORMAT(ctes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $return;
    }

    public function getCteSaidaMensal($issuer)
    {

        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $return = DB::select("SELECT SUM(ctes.vCTe) AS price,  DATE_FORMAT(ctes.data_emissao, '%m-%Y') AS date FROM ctes where ctes.emitente_cnpj = ? and ctes.data_emissao >= ? and ctes.status_cte = 100 GROUP BY DATE_FORMAT(ctes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $return;
    }

    public function getICMSSTSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vST) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.emitente_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getICMSEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vICMS) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.destinatario_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getICMSSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vICMS) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.emitente_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getIPIEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vIPI) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.destinatario_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getIPISaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vIPI) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.emitente_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getPISEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vPIS) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.destinatario_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getPISSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vPIS) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.emitente_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getCOFINSEntradaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vCOFINS) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.destinatario_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getCOFINSSaidaMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfes.vCOFINS) AS price,  DATE_FORMAT(nfes.data_emissao, '%m-%Y') AS date FROM nfes where nfes.emitente_cnpj = ? and nfes.data_emissao >= ? and nfes.status_nota = 100 GROUP BY DATE_FORMAT(nfes.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }

    public function getSaidaNFSEMensal($issuer)
    {
        $min_config = config('admin.min_months_to_display');
        $data_emissao = now()->subMonth($min_config)->startOfMonth()->format('Y-m-d 00:00:00');

        $values = DB::select("SELECT SUM(nfses.valor_servico) AS price,  DATE_FORMAT(nfses.data_emissao, '%m-%Y') AS date FROM nfses where nfses.prestador_cnpj = ? and nfses.data_emissao >= ? and nfses.cancelada = 0 GROUP BY DATE_FORMAT(nfses.data_emissao, '%m-%Y') ORDER BY 2 ASC", [
            $issuer->cnpj,
            $data_emissao,
        ]);

        return $values;
    }
}
