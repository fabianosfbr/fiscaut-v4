<?php

namespace App\Filament\Pages\Relatorio;

use App\Filament\Widgets\StatisticData;
use App\Models\Issuer;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EntradaSaida extends Page
{
    protected static ?string $navigationLabel = 'Entrada vs Saída';

    protected static ?string $title = 'Relatório Entrada vs Saída';

    protected static ?string $slug = 'entrada-saida';

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';

    public ?Issuer $issuer = null;

    public array $rows = [];

    public float $totalEntradas = 0.0;

    public float $totalSaidas = 0.0;

    public float $totalResultado = 0.0;

    protected string $view = 'filament.pages.relatorio.entrada-saida';

    public function mount(): void
    {
        $this->issuer = Auth::user()?->currentIssuer;
        $this->generateData($this->issuer);
    }

    public function generateData(?Issuer $issuer): void
    {
        $this->rows = [];
        $this->totalEntradas = 0.0;
        $this->totalSaidas = 0.0;
        $this->totalResultado = 0.0;

        if ($issuer === null) {
            return;
        }

        $months = StatisticData::faturamentoMensal($issuer);

        foreach ($months as $monthKey => $values) {
            $entradas = (float) ($values['expense'] ?? 0.0);
            $saidas = (float) ($values['income'] ?? 0.0);
            $resultado = $entradas - $saidas;

            $this->rows[] = [
                'data_ref' => (string) $monthKey,
                'entradas' => $entradas,
                'saidas' => $saidas,
                'resultado' => $resultado,
            ];

            $this->totalEntradas += $entradas;
            $this->totalSaidas += $saidas;
            $this->totalResultado += $resultado;
        }
    }
}
