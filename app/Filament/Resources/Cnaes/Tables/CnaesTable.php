<?php

namespace App\Filament\Resources\Cnaes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CnaesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('anexo')
                    ->label('Anexo'),
                IconColumn::make('fator_r')
                    ->label('Fator R')->boolean(),
                TextColumn::make('aliquota')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.').'%')
                    ->label('Alíquota'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
