<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use App\Filament\Forms\Components\PresetView;
use App\Filament\Forms\Concerns\HasTableViews;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListIssuerControls extends ListRecords
{
    use HasTableViews;

    protected static string $resource = IssuerControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }

    public function getPresetTableViews(): array
    {
        return [

            'overdue_controls' => PresetView::make('Controles Vencidos')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('issuer_id', currentIssuer()->id)->where('data_programada', '<', now())),

            'overdue_7days_controls' => PresetView::make('Próximos 7 dias')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('data_programada', [now(), now()->addDays(7)])),

            'overdue_15days_controls' => PresetView::make('Próximos 15 dias')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('data_programada', [now(), now()->addDays(15)])),

            'overdue_30days_controls' => PresetView::make('Próximos 30 dias')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('data_programada', [now(), now()->addDays(30)])),
        ];
    }
}
