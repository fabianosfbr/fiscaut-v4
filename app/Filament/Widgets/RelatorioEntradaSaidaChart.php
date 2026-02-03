<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class RelatorioEntradaSaidaChart extends ChartWidget
{
    protected ?string $heading = 'Entrada vs Saída';

    protected function getData(): array
    {
        $issuer = Auth::user()?->currentIssuer;

        if ($issuer === null) {
            return [
                'datasets' => [
                    [
                        'label' => 'Entrada',
                        'backgroundColor' => 'rgba(102, 126, 234, 0.25)',
                        'borderColor' => 'rgba(102, 126, 234, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'data' => [],
                        'pointRadius' => 0,
                        'tension' => 0.35,
                    ],
                    [
                        'label' => 'Saída',
                        'backgroundColor' => 'rgba(236, 72, 153, 0.25)',
                        'borderColor' => 'rgba(236, 72, 153, 1)',
                        'borderWidth' => 2,
                        'fill' => true,
                        'data' => [],
                        'pointRadius' => 0,
                        'tension' => 0.35,
                    ],
                ],
                'labels' => [],
            ];
        }

        $data = StatisticData::faturamentoMensal($issuer);
        $labels = array_keys($data);

        $entradas = array_map(
            fn (array $value): float => (float) ($value['expense'] ?? 0.0),
            $data,
        );

        $saidas = array_map(
            fn (array $value): float => (float) ($value['income'] ?? 0.0),
            $data,
        );

        return [
            'datasets' => [
                [
                    'label' => 'Entrada',
                    'backgroundColor' => 'rgba(102, 126, 234, 0.25)',
                    'borderColor' => 'rgba(102, 126, 234, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'data' => $entradas,
                    'pointRadius' => 0,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Saída',
                    'backgroundColor' => 'rgba(236, 72, 153, 0.25)',
                    'borderColor' => 'rgba(236, 72, 153, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'data' => $saidas,
                    'pointRadius' => 0,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'align' => 'end',
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
            ],
        ];
    }
}

