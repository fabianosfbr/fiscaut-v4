<?php

namespace App\Filament\Resources\Bancos\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class BancosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('issuer_id', Auth::user()->currentIssuer->id);
            })
            ->columns([
                TextColumn::make('nome')
                    ->searchable()
                    ->label('Nome'),
                TextColumn::make('cnpj')
                    ->label('CNPJ'),
                TextColumn::make('plano_de_conta')
                    ->label('Conta Contábil')
                    ->formatStateUsing(function ($state) {
                        return $state->codigo . ' | ' . $state->nome;
                    })
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
