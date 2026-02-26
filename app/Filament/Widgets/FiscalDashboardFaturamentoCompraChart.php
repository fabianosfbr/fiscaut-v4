<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class FiscalDashboardFaturamentoCompraChart extends ChartWidget
{
    protected ?string $heading = 'Faturamento Compra';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $issuer = Auth::user()->currentIssuer;
        $data = StatisticData::faturamentoMensal($issuer);

        $data = array_reverse($data);
        $labels = array_keys($data);
        $faturamento = array_map(function ($value) {
            return $value['income'];
        }, $data);
        $compra = array_map(function ($value) {
            return $value['expense'];
        }, $data);

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento',
                    'backgroundColor' => 'rgba(102, 126, 234, 0.5)',
                    'borderWidth' => 1,
                    'tension' => 0.1,
                    'data' => $faturamento,
                    'tooltip' => [
                        'callbacks' => [],
                    ],
                    'fill' => [
                        'target' => 'origin',
                    ],

                ],
                [
                    'label' => 'Compra',
                    'backgroundColor' => 'rgba(237, 100, 166, 0.5)',
                    'borderWidth' => 1,
                    'tension' => 0.1,
                    'data' => $compra,
                    'fill' => [
                        'target' => 'origin',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
