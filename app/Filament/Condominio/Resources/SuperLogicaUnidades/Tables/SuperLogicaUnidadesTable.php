<?php

namespace App\Filament\Condominio\Resources\SuperLogicaUnidades\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuperLogicaUnidadesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('id_condominio', currentIssuer()->superlogica_condominio_id);
            })
            ->recordUrl(null)
            ->columns([
                // TextColumn::make('id_unidade_uni')
                //     ->label('ID Unidade Uni')
                //     ->state(fn($record) => dd($record->metadados)),
                TextColumn::make('metadados.st_unidade_uni')
                    ->label('Unidade'),
                TextColumn::make('metadados.st_bloco_uni')
                    ->label('Bloco'),
                TextColumn::make('metadados.nome_proprietario')
                    ->label('Proprietário')
                    ->searchable(),
                TextColumn::make('metadados.celular_proprietario')
                    ->label('Celular Proprietário'),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
