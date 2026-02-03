<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class FiscalDashboardProdutoVendivoChart extends ChartWidget
{
    protected ?string $heading = 'Produtos mais vendidos';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $issuer = Auth::user()->currentIssuer;
        if ($issuer === null) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $response = StatisticData::produtosMaisVendidos($issuer);

        $response = array_reverse($response);

        $data = [];
        foreach ($response as $value) {
            array_push($data, $value);
        }

        $data = collect($data);

        return [
            'labels' => $data->pluck('label')->toArray(),
            'datasets' => [
                [
                    'label' => 'Produtos mais vendidos',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        'rgba(138, 245, 39, 0.8)',
                        'rgba(39, 159, 245, 0.8)',
                        'rgba(245, 39, 223, 0.8)',
                        'rgba(245, 129, 39, 0.8)',
                        'rgba(39, 245, 43, 0.8)',
                        'rgba(39, 189, 245, 0.8)',
                        'rgba(39, 189, 176, 0.8)',
                        'rgba(39, 115, 176, 0.83)',
                        'rgba(39, 67, 176, 0.83)',
                        'rgba(45, 232, 176, 0.83)',
                        'rgba(150, 232, 54, 0.83)',
                        'rgba(150, 137, 54, 0.83)',
                        'rgba(138, 234, 39, 0.83)',
                        'rgba(40, 117, 235, 0.83)',
                        'rgba(235, 40, 194, 0.83)',
                    ],

                ],

            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'delay' => 1000,
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                    'labels' => [
                        // 'color' => 'rgb(255, 99, 132)'
                    ],
                ],

            ],
        ];
    }
}
