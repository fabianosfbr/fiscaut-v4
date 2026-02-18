<?php

namespace App\Filament\Resources\ParametroSuperLogicas\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\ParametroSuperLogicaExporter;

class ParametroSuperLogicasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', Auth::user()->currentIssuer->id);
            })
            ->columns([
                TextColumn::make('params')
                    ->label('Parametros')
                    ->badge()
                    ->color('gray')
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query->SearchByParametro(search: $search)),

                TextColumn::make('contaCredito')
                    ->label('Conta crédito')
                    ->formatStateUsing(function ($state) {
                        return $state?->codigo;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state?->codigo . ' | ' . $state?->nome;
                    })
                    ->badge(),

                TextColumn::make('contaDebito')
                    ->label('Conta débito')
                    ->formatStateUsing(function ($state) {
                        return $state?->codigo;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return $state?->codigo . ' | ' . $state?->nome;
                    })
                    ->badge(),

                TextColumn::make('codigo_historico')
                    ->label('Cód. Histórico')
                    ->badge(),

                IconColumn::make('check_value')
                    ->label('Verificar valor')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Exportar dados')
                        ->modalHeading('Exportar dados')
                        ->modalDescription('Exportar os registros selecionados para formato .xlsx e .csv')
                        ->modalWidth('md')
                        ->exporter(ParametroSuperLogicaExporter::class)
                        ->columnMapping(false)
                        ->color('primary'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
