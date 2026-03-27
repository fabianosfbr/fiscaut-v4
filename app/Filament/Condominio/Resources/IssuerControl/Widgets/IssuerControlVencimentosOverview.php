<?php

namespace App\Filament\Condominio\Resources\IssuerControl\Widgets;

use App\Models\IssuerControl;
use Filament\Widgets\Widget;

class IssuerControlVencimentosOverview extends Widget
{
    protected string $view = 'filament.condominio.widgets.issuer-control-vencimentos-overview';

    protected function getViewData(): array
    {
        $issuer = currentIssuer();

        $vencidos = 0;
        $proximos7 = 0;
        $proximos15 = 0;
        $proximos30 = 0;

        if ($issuer) {
            $vencidos = IssuerControl::query()
                ->where('issuer_id', $issuer->id)
                ->where('data_programada', '<', now()->toDateString())
                ->whereIn('status', ['programada', 'em_andamento'])
                ->count();

            $proximos7 = IssuerControl::query()
                ->where('issuer_id', $issuer->id)
                ->whereBetween('data_programada', [now(), now()->addDays(7)])
                ->count();

            $proximos15 = IssuerControl::query()
                ->where('issuer_id', $issuer->id)
                ->whereBetween('data_programada', [now(), now()->addDays(15)])
                ->count();

            $proximos30 = IssuerControl::query()
                ->where('issuer_id', $issuer->id)
                ->whereBetween('data_programada', [now(), now()->addDays(30)])
                ->count();
        }

        return [
            'vencidos' => $vencidos,
            'proximos7' => $proximos7,
            'proximos15' => $proximos15,
            'proximos30' => $proximos30,
        ];
    }
}
