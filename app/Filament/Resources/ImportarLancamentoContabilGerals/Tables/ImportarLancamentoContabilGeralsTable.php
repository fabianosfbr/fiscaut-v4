<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
                    ->whereJsonContains('metadata->type', 'geral')
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
                Filter::make('debito')
                    ->schema([
                        TextInput::make('debito')
                            ->label('Débito')
                            ->placeholder('Ex: 5102, 6108'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $input = (string) ($data['debito'] ?? '');

                        $debitosList = array_values(array_filter(
                            array_map(
                                static fn (string $value): string => trim($value),
                                preg_split('/[,\s;]+/', $input, -1, PREG_SPLIT_NO_EMPTY) ?: []
                            ),
                            static fn (string $value): bool => $value !== ''
                        ));

                        if ($debitosList === []) {
                            return $query;
                        }

                        return $query->whereIn('debito', $debitosList);
                    }),

                Filter::make('credito')
                    ->schema([
                        TextInput::make('credito')
                            ->label('Crédito')
                            ->placeholder('Ex: 5102, 6108'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $input = (string) ($data['credito'] ?? '');

                        $creditosList = array_values(array_filter(
                            array_map(
                                static fn (string $value): string => trim($value),
                                preg_split('/[,\s;]+/', $input, -1, PREG_SPLIT_NO_EMPTY) ?: []
                            ),
                            static fn (string $value): bool => $value !== ''
                        ));

                        if ($creditosList === []) {
                            return $query;
                        }

                        return $query->whereIn('credito', $creditosList);
                    }),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
