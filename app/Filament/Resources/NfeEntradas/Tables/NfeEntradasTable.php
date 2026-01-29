<?php

namespace App\Filament\Resources\NfeEntradas\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Models\NotaFiscalEletronica;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class NfeEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data_emissao', 'desc')
            ->paginated([10, 25, 50, 100])
            ->recordUrl(null)
            ->columns([
                TextColumn::make('nNF')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('emitente_razao_social')
                    ->label('Empresa')
                    ->limit(30)
                    ->searchable()
                    ->size('sm')
                    ->description(function (NotaFiscalEletronica $record) {

                        return $record->emitente_cnpj;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->toggleable(),

                TextColumn::make('data_entrada')
                    ->label('Entrada')
                    ->toggleable()
                    ->date('d/m/Y'),

                TextColumn::make('vNfe')
                    ->label('Valor Total')
                    ->sortable()
                    ->money('BRL'),

                TextColumn::make('status_nota')
                    ->label('Status')
                    ->toggleable()
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
