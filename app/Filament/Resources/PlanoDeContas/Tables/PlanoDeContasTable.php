<?php

namespace App\Filament\Resources\PlanoDeContas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PlanoDeContasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->where('issuer_id', Auth::user()->currentIssuer->id);
            })
            ->recordUrl(null)
            ->defaultSort('classificacao', 'asc')
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->alignEnd(),
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->alignEnd(),
                TextColumn::make('classificacao')
                    ->label('Classificação')
                    ->searchable()
                    ->alignEnd(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                IconColumn::make('is_ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Tipo da conta')
                    ->placeholder('Todas')
                    ->trueLabel('Analítica')
                    ->falseLabel('Sintética')
                    ->queries(
                        true: fn (Builder $query) => $query->where('tipo', 'A'),
                        false: fn (Builder $query) => $query->where('tipo', 'S'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
