<?php

use Carbon\Carbon;
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

    public array $recebimentos = [];

    public function mount(array $recebimentos)
    {
        $this->recebimentos = $recebimentos;
    }

    public function table(Table $table): Table
    {
        return $table

            ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {
                $records = collect($this->recebimentos['recebimento'])->forPage($page, $recordsPerPage);

                return new LengthAwarePaginator(
                    $records,
                    total: count($this->recebimentos['recebimento']), // Total number of records across all pages
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->columns([
                TextColumn::make('dt_vencimento_recb')
                    ->label('Vencimento')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d/m/Y')),
                TextColumn::make('dt_competencia_recb')
                    ->label('Competência')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('m/Y')),
                TextColumn::make('encargos.0.diasatraso')
                    ->label('Atraso'),
                TextColumn::make('id_recebimento_recb')
                    ->label('Código'),
                TextColumn::make('vl_emitido_recb')
                    ->label('Principal')
                    ->money('BRL'),
                TextColumn::make('encargos.0.detalhes.juros')
                    ->label('Juros')
                    ->money('BRL'),
                TextColumn::make('encargos.0.detalhes.multa')
                    ->label('Multa')
                    ->money('BRL'),
                TextColumn::make('encargos.0.detalhes.atualizacaomonetaria')
                    ->label('Atualização')
                    ->money('BRL'),
                TextColumn::make('encargos.0.detalhes.honorarios')
                    ->label('Honorários')
                    ->money('BRL'),
                TextColumn::make('encargos.0.valorcorrigido')
                    ->label('Total')
                    ->money('BRL'),

            ]);
    }
};
