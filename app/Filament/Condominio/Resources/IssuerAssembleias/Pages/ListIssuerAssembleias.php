<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Pages;

use App\Enums\IssuerAgeTypeEnum;
use App\Enums\IssuerAssembleiaPrazoTecnicoEnum;
use App\Filament\Condominio\Resources\IssuerAssembleias\IssuerAssembleiaResource;
use App\Filament\Forms\Components\PresetView;
use App\Filament\Forms\Concerns\HasTableViews;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListIssuerAssembleias extends ListRecords
{
    use HasTableViews;

    protected static string $resource = IssuerAssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'ago' => Tab::make()
                ->label('AGO')
                ->modifyQueryUsing(fn (Builder $query) => $query->porTipo(IssuerAgeTypeEnum::AGO)),
            'age' => Tab::make()
                ->label('AGE')
                ->modifyQueryUsing(fn (Builder $query) => $query->porTipo(IssuerAgeTypeEnum::AGE)),

        ];
    }

    public function getPresetTableViews(): array
    {
        return [
            'atrasadas' => PresetView::make('Atrasadas')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->atrasadas()),
            'antes_do_prazo' => PresetView::make('Antes do Prazo')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->porPrazoTecnicoStatus(IssuerAssembleiaPrazoTecnicoEnum::ANTES_DO_PRAZO)),
            'primeiro' => PresetView::make('1º Prazo Técnico')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->porPrazoTecnicoStatus(IssuerAssembleiaPrazoTecnicoEnum::PRIMEIRO)),
            'segundo' => PresetView::make('2º Prazo Técnico')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->porPrazoTecnicoStatus(IssuerAssembleiaPrazoTecnicoEnum::SEGUNDO)),
            'terceiro' => PresetView::make('3º Prazo Técnico')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->porPrazoTecnicoStatus(IssuerAssembleiaPrazoTecnicoEnum::TERCEIRO)),
            'quarto' => PresetView::make('4º Prazo Técnico')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->porPrazoTecnicoStatus(IssuerAssembleiaPrazoTecnicoEnum::QUARTO)),
        ];
    }
}
