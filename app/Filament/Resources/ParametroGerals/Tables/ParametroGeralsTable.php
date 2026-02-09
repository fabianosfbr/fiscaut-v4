<?php

namespace App\Filament\Resources\ParametroGerals\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ParametroGeralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('params')
                    ->label('Parametros')
                    ->badge()
                    ->color('gray')
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query->SearchByParametro(search: $search)),

                TextColumn::make('plano_de_conta')
                    ->label('Conta Contábil')
                    ->limit(30)
                    ->formatStateUsing(function ($state) {
                        return $state->codigo . ' | ' . $state->nome;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state?->codigo . ' | ' . $state?->nome) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state?->codigo . ' | ' . $state?->nome;
                    })
                    ->color('gray')
                    ->badge(),
                TextColumn::make('codigo_historico')
                    ->label('Cód. Histórico')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
