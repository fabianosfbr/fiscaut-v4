<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Pages;

use App\Enums\IssuerAgeTypeEnum;
use App\Filament\Condominio\Resources\IssuerAssembleias\IssuerAssembleiaResource;
use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaPrazoTecnicoOverview;
use App\Filament\Condominio\Resources\IssuerAssembleias\Widgets\IssuerAssembleiaSindicoMandatoOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListIssuerAssembleias extends ListRecords
{
    protected static string $resource = IssuerAssembleiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // IssuerAssembleiaPrazoTecnicoOverview::class,
            // IssuerAssembleiaSindicoMandatoOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'ago' => Tab::make()
                ->label('AGO')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', IssuerAgeTypeEnum::AGO)),
            'age' => Tab::make()
                ->label('AGE')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', IssuerAgeTypeEnum::AGE)),

        ];
    }
}
