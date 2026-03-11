<?php

namespace App\Filament\Condominio\Resources\IssuerAges\Pages;

use App\Enums\IssuerAgeTypeEnum;
use App\Filament\Condominio\Resources\IssuerAges\IssuerAgeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListIssuerAges extends ListRecords
{
    protected static string $resource = IssuerAgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar Novo')
                ->modalHeading('Adicionar Novo Documento')
                ->mutateDataUsing(function (array $data): array {

                    $data['issuer_id'] = currentIssuer()->id;
                    $data['tenant_id'] = currentIssuer()->tenant_id;

                    return IssuerAgeResource::cleanData($data);
                }),
        ];
    }


    public function getTabs(): array
    {
        return [
            'age' => Tab::make()
                ->label('AGE')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', IssuerAgeTypeEnum::AGE)),
            'ago' => Tab::make()
                ->label('AGO')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', IssuerAgeTypeEnum::AGO)),
        ];
    }


}
