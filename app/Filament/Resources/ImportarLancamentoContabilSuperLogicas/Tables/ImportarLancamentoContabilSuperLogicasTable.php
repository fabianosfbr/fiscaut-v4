<?php

namespace App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ImportarLancamentoContabilSuperLogicasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('issuer_id', Auth::user()->currentIssuer->id)
                    ->whereJsonContains('metadata->type', 'super_logica');
            })
            ->columns([
                TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y'),

                TextColumn::make('debito')
                    ->label('Débito')
                    ->searchable()
                    ->tooltip(function ($state, $record) {
                        return $record->metadata['row']['conta_debito_descr'] ?? '';
                    })
                    ->badge()
                    ->color('success')
                    ->copyable(),

                TextColumn::make('credito')
                    ->label('Crédito')
                    ->searchable()
                    ->tooltip(function ($state, $record) {
                        return $record->metadata['row']['conta_credito_descr'] ?? '';
                    })
                    ->badge()
                    ->color('danger')
                    ->copyable(),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(function ($state) {
                        if ($state == 0) {
                            return '';
                        }

                        return 'R$ '.number_format($state, 2, ',', '.');
                    }),

                TextColumn::make('historico')
                    ->label('Histórico Contábil')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_exist')
                    ->label('Possui Vínculo')
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $data['value']
                            ? $query->whereNotNull('historico')
                            : $query->whereNull('historico');
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
