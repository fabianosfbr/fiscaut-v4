<?php

namespace App\Filament\Pages\Relatorio;

use App\Filament\Widgets\StatisticData;
use App\Models\Issuer;
use Filament\Pages\Page;
use UnitEnum;

class EntradaVsImposto extends Page
{
    protected static ?string $navigationLabel = 'Entrada vs Imposto';

    protected static ?string $title = 'Relatório Entrada vs Imposto';

    protected static ?string $slug = 'entrada-vs-imposto';

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';

    protected string $view = 'filament.pages.relatorio.entrada-vs-imposto';

    public ?Issuer $issuer = null;

    public array $faturamento = [];

    public function mount(): void
    {
        $this->issuer = currentIssuer();
        $this->generateData($this->issuer);
    }

    public function generateData(?Issuer $issuer): void
    {
        $this->faturamento = [];

        if ($issuer === null) {
            return;
        }

        $months = StatisticData::entradaVsImpostoMensal($issuer);

        if (! is_array($months) || $months === []) {
            $this->faturamento = [];

            return;
        }

        $this->faturamento = $this->normalizeMonthlyRows($months);
    }

    private function normalizeMonthlyRows(array $months): array
    {
        $defaults = [
            'faturamento' => 0.0,
            'faturamento-nfse' => 0.0,
            'icms' => 0.0,
            'icmsST' => 0.0,
            'ipi' => 0.0,
            'pis' => 0.0,
            'cofins' => 0.0,
            'cprb' => 0.0,
            'csll' => 0.0,
            'irpj' => 0.0,
            'faturamentoLiquido' => 0.0,
        ];

        $normalized = [];

        foreach ($months as $monthKey => $row) {
            if (! is_array($row)) {
                $row = [];
            }

            $row = array_replace($defaults, $row);

            foreach ($defaults as $key => $defaultValue) {
                $row[$key] = (float) ($row[$key] ?? $defaultValue);
            }

            $normalized[(string) $monthKey] = $row;
        }

        return $normalized;
    }
}
