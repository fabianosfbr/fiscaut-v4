<?php

namespace App\Filament\Resources\CteTomadas\Pages;

use App\Filament\Resources\CteTomadas\CteTomadaResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;

class ListCteTomadas extends ListRecords
{
    protected static string $resource = CteTomadaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return 'Conhecimentos de Transporte Eletrônicos';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getTabs(): array
    {
        $issuer = currentIssuer();

        return [
            'entrada' => Tab::make()
                ->label('CTe NF Entrada')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereJsonContains('metadata', ['nfe_destinatario_cnpj' => $issuer->cnpj])),
            'saida' => Tab::make()
                ->label('CTe NF Saídas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereJsonContains('metadata', ['nfe_emitente_cnpj' => $issuer->cnpj])),
            'all' => Tab::make()
                ->label('Todos'),
        ];
    }
}
