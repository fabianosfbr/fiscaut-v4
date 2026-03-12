<?php

namespace App\Filament\Condominio\Resources\IssuerGroupControls\Tables;


use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IssuerGroupControlsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order', 'asc')
            ->reorderable('order')
            ->columns([
                TextColumn::make("order")
                    ->label("Ordem")
                    ->searchable()
                    ->sortable(),
                TextColumn::make("name")
                    ->label("Nome")
                    ->searchable()
                    ->sortable(),
                TextColumn::make("description")
                    ->label("Descrição")
                    ->searchable()
                    ->sortable(),
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
