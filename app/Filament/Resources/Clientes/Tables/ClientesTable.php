<?php

namespace App\Filament\Resources\Clientes\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('issuer_id', Auth::user()->currentIssuer->id);
            })
            ->defaultSort('conta_contabil', 'asc')
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable(),
                TextColumn::make('conta_contabil')
                    ->label('Conta Contábil')
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
