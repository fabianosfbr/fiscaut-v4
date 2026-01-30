<?php

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public array $difals = [];

    public function mount(array $difals)
    {
        $this->difals = $difals;
    }

    public function table(Table $table): Table
    {
        return $table

            ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {
                $records = collect($this->difals)->forPage($page, $recordsPerPage);

                return new LengthAwarePaginator(
                    $records,
                    total: count($this->difals), // Total number of records across all pages
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código'),
                TextColumn::make('produto')
                    ->label('Descrição')
                    ->limit(30),
                TextColumn::make('cfop')
                    ->label('CFOP'),
                TextColumn::make('valor_produto')
                    ->label('Valor Produto')
                    ->money('BRL'),
                TextColumn::make('base_calculo')
                    ->label('Base de Cálculo')
                    ->money('BRL'),
                TextColumn::make('aliquota_interestadual')
                    ->label('Alíq. Origem')
                    ->formatStateUsing(fn ($state) => number_format($state, 0).'%'),
                TextColumn::make('aliquota_interna_destino')
                    ->label('Alíq. Destino')
                    ->formatStateUsing(fn ($state) => number_format($state, 0).'%'),
                TextColumn::make('difal')
                    ->label('Valor DIFAL')
                    ->money('BRL'),
            ]);
    }
};
