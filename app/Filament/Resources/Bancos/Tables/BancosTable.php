<?php

namespace App\Filament\Resources\Bancos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                        return $state->codigo.' | '.$state->nome;
                    }),
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
