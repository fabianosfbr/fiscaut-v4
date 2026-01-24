<?php

namespace App\Filament\Resources\CodigosServicos\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CodigosServicosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('codigo', 'asc')
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('cnae.codigo')
                    ->label('CNAE')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('cnae_id')
                    ->label('CNAE')
                    ->relationship('cnae', 'descricao')
                    ->searchable()
                    ->preload(),
                Filter::make('com_cnae')
                    ->label('Com CNAE Vinculado')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('cnae_id'))
                    ->toggle(),
                Filter::make('sem_cnae')
                    ->label('Sem CNAE Vinculado')
                    ->query(fn(Builder $query): Builder => $query->whereNull('cnae_id'))
                    ->toggle(),
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
