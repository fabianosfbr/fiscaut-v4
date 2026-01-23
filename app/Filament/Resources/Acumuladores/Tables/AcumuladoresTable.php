<?php

namespace App\Filament\Resources\Acumuladores\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class AcumuladoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', Auth::user()->issuer_id);
            })
            ->columns([
                TextColumn::make('codi_acu')
                    ->label('Código Acumulador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nome_acu')
                    ->label('Nome')
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
