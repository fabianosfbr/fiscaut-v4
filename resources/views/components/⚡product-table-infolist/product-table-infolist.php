<?php

use Livewire\Component;

use Filament\Tables\Table;
use App\Models\NotaFiscalEletronica;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;


new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public array $products = [];

    public function mount(array $products)
    {
        $this->products = $products;
    }

    public function table(Table $table): Table
    {
        return $table

            ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {
                $records = collect($this->products)->forPage($page, $recordsPerPage);

                return new LengthAwarePaginator(
                    $records,
                    total: count($this->products), // Total number of records across all pages
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->columns([
                TextColumn::make('cProd')
                    ->label('Código'),
                TextColumn::make('xProd')
                    ->label('Descrição')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
                TextColumn::make('NCM')
                    ->label('NCM'),
                TextColumn::make('CFOP')
                    ->label('CFOP'),
                TextColumn::make('uCom')
                    ->label('Unidade')
                    ->alignCenter(),
                TextColumn::make('vUnCom')
                    ->label('Valor Unit')
                    ->money('BRL'),

                TextColumn::make('impostos.vBC')
                    ->label('B.ICMS')
                    ->money('BRL'),
                TextColumn::make('impostos.pICMS')
                    ->label('% ICMS')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('impostos.vICMS')
                    ->label('V.ICMS')
                    ->money('BRL'),
                TextColumn::make('vProd')
                    ->label('Valor Total')
                    ->money('BRL'),
            ]);
    }
};
