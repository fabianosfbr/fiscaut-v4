<?php

namespace App\Filament\Resources\Cfops\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CfopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('anexo', 'desc')
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->sortable(),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
                TextColumn::make('anexo')
                    ->label('Tributação')
                    ->sortable(),
                IconColumn::make('is_faturamento')
                    ->label('Compõe Faturamento')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
