<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class RelatorioFaturamentoMensalChart extends ChartWidget
{
    protected ?string $heading = 'Faturamento Mensal';

    protected function getData(): array
    {
        $issuer = Auth::user()?->currentIssuer;

        if ($issuer === null) {
            return [
                'datasets' => [
                    [
                        'label' => 'Faturamento',
                        'backgroundColor' => 'rgba(102, 126, 234, 0.5)',
                        'borderWidth' => 1,
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $data = StatisticData::faturamentoMensal($issuer);
        $data = array_reverse($data);

        $labels = array_keys($data);
        $faturamento = array_map(
            fn (array $value): float => (float) ($value['income'] ?? 0.0),
            $data,
        );

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento',
                    'backgroundColor' => 'rgba(102, 126, 234, 0.5)',
                    'borderWidth' => 1,
                    'data' => $faturamento,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

