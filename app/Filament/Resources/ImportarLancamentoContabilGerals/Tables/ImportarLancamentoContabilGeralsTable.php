<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImportarLancamentoContabilGeralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                return $query->where('user_id', Auth::user()->id)
                    ->where('valor', '!=', 0)
                    ->where('issuer_id', $user->currentIssuer->id);
            })
            ->columns([
                TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y'),

                TextColumn::make('debito')
                    ->label('Débito')
                    ->searchable()
                    ->badge()
                    ->tooltip(function (Model $record) {
                        if (! is_null($record->metadata) && isset($record->metadata['descricao_debito'])) {
                            return $record->metadata['descricao_debito'];
                        }

                        return null;
                    })
                    ->color('success')
                    ->copyable(),

                TextColumn::make('credito')
                    ->label('Crédito')
                    ->searchable()
                    ->badge()
                    ->tooltip(function (Model $record) {
                        if (! is_null($record->metadata) && isset($record->metadata['descricao_credito'])) {

                            return $record->metadata['descricao_credito'];
                        }

                        return null;
                    })
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

                TextColumn::make('metadata.texto_linha')
                    ->label('Histórico Texto')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_exist')
                    ->label('Vínculo')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_exist')
                    ->label('Possui Vínculo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
