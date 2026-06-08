<?php

namespace App\Filament\Resources\NcmRestricoes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NcmRestricoesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('grupo')
            ->columns([
                TextColumn::make('grupo')
                    ->label('Grupo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'MON') => 'warning',
                        str_starts_with($state, 'ISE') => 'info',
                        str_starts_with($state, 'SUP') => 'gray',
                        default => 'success',
                    }),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ALIQUOTA_ZERO' => 'success',
                        'MONOFASICO' => 'warning',
                        'SUSPENSAO' => 'gray',
                        'ISENCAO' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('tipo_match')
                    ->label('Match'),
                TextColumn::make('fundamento')
                    ->label('Fundamento')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo de restrição')
                    ->options([
                        'ALIQUOTA_ZERO' => 'Alíquota Zero',
                        'MONOFASICO' => 'Monofásico',
                        'SUSPENSAO' => 'Suspensão',
                        'ISENCAO' => 'Isenção',
                    ]),
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
