<?php

namespace App\Filament\Pages\Relatorio;

use App\Filament\Widgets\StatisticData;
use App\Models\Issuer;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class Faturamento extends Page
{
    protected static ?string $navigationLabel = 'Faturamento';

    protected static ?string $title = 'Relatório Faturamento';

    protected static ?string $slug = 'faturamento';

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';

    protected $listeners = ['updateGraphic' => '$refresh'];

    public array $faturamento = [];

    public ?Issuer $issuer = null;

    public array $rows = [];

    public float $total = 0.0;

    protected string $view = 'filament.pages.relatorio.faturamento';

    public function mount(): void
    {
        $this->issuer = Auth::user()?->currentIssuer;
        $this->generateData($this->issuer);
    }

    public function generateData(?Issuer $issuer): void
    {
        $this->faturamento = [];
        $this->rows = [];
        $this->total = 0.0;

        if ($issuer === null) {
            return;
        }

        $months = StatisticData::faturamentoMensal($issuer);

        $this->faturamento = $months;

        foreach ($months as $monthKey => $values) {
            $income = (float) ($values['income'] ?? 0.0);

            $this->rows[] = [
                'data_ref' => (string) $monthKey,
                'faturamento' => $income,
            ];

            $this->total += $income;
        }
    }

    public function gerarDeclaracao(): void
    {
        Notification::make()
            ->title('Em desenvolvimento')
            ->body('A geração da declaração de faturamento mensal será disponibilizada em breve.')
            ->info()
            ->send();
    }
}
