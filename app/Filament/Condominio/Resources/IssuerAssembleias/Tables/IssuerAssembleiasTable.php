<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Tables;

use App\Enums\IssuerAgeTypeEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class IssuerAssembleiasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', currentIssuer()->id);
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(IssuerAgeTypeEnum $state): string => match ($state) {
                        IssuerAgeTypeEnum::AGO => 'success',
                        IssuerAgeTypeEnum::AGE => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('document_path')
                    ->label('Documento')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return basename($state);
                    })
                    ->formatStateUsing(fn($state) => basename($state)),

                // AGE Only
                TextColumn::make('vigencia_date')
                    ->label('Vigência')
                    ->visible(fn($livewire) => $livewire->activeTab === 'age')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico')
                    ->label('Prazo Técnico')
                    ->visible(fn($livewire) => $livewire->activeTab === 'age')
                    ->sortable(),

                // ColorColumn::make('status')
                //     ->label('Status')
                //     ->state(function ($record) {
                //         $hoje = now();
                //         $dataRef = $record->data_limite_edital->subDays($record->prazo_tecnico_edital)->format('d/m/Y');

                //         if ($hoje < $dataRef) {
                //             return '#dc3545';
                //         }

                //         return '#67ac0eff';
                //     }),


                // AGO Only
                TextColumn::make('data_limite_ago')
                    ->label('Data Limite')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico_edital')
                    ->label('Prazo Técnico (Edital)')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('mandato_fim')
                    ->label('Fim Mandato (Síndico)')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('prazo_tecnico_mandato')
                    ->label('Prazo Técnico (Mandato)')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('mandato_conselho_fim')
                    ->label('Fim Mandato (Conselho)')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico_mandato_conselho')
                    ->label('Prazo Técnico (Conselho)')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('mandato_banco_fim')
                    ->label('Fim Mandato (Banco)')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico_mandato_banco')
                    ->label('Prazo Técnico (Banco)')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                // Boleto fields (AGO)
                TextColumn::make('boleto_dia_vencimento')
                    ->label('Dia Vencimento')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),

                TextColumn::make('boleto_tipo_prazo')
                    ->label('Tipo Prazo')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'uteis' => 'Dias Úteis',
                        'corridos' => 'Dias Corridos',
                        default => (string) $state,
                    }),

                TextColumn::make('boleto_gerado_por')
                    ->label('Gerado Por')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'administradora' => 'Administradora',
                        'garantidora' => 'Garantidora',
                        default => (string) $state,
                    }),

                TextColumn::make('boleto_forma_rateio')
                    ->label('Forma Rateio')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'ideal' => 'Rateio Ideal',
                        'unidade' => 'Unidade',
                        'm2' => 'Por m²',
                        default => (string) $state,
                    }),

                // Isenção/Remuneração (AGO)
                TextColumn::make('tem_isencao_remuneracao')
                    ->label('Tipo')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state): string => $state ? 'Isenção' : 'Remuneração')
                    ->badge()
                    ->color(fn($state): string => $state ? 'success' : 'info'),

                TextColumn::make('quem_recebe_isencao')
                    ->label('Quem Recebe')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),

                TextColumn::make('valor_isencao_remuneracao')
                    ->label('Valor')
                    ->visible(fn($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->money('BRL'),

                // Common
                TextColumn::make('data_limite_edital')
                    ->label('Data Limite Edital')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('data_limite_edital')
                    ->label('Data Limite Edital')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('de')
                            ->label('Data Limite Início')
                            ->columnSpan(1),
                        DatePicker::make('ate')
                            ->label('Data Limite Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['de']) && empty($data['ate'])) {
                            return null;
                        }

                        $inicio = $data['de'] ? date('d/m/Y', strtotime($data['de'])) : '...';
                        $fim = $data['ate'] ? date('d/m/Y', strtotime($data['ate'])) : '...';

                        return "Data Limite: {$inicio} até {$fim}";
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['de'])) {
                            $query->whereDate('data_limite_edital', '>=', $data['de']);
                        }
                        if (! empty($data['ate'])) {
                            $query->whereDate('data_limite_edital', '<=', $data['ate']);
                        }

                        return $query;
                    }),

                Filter::make('vigencia_date')
                    ->label('Vigência')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('de')
                            ->label('Data Limite Início')
                            ->columnSpan(1),
                        DatePicker::make('ate')
                            ->label('Data Limite Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['de'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('vigencia_date', '>=', $date),
                            )
                            ->when(
                                $data['ate'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('vigencia_date', '<=', $date),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (empty($data['de']) && empty($data['ate'])) {
                            return null;
                        }

                        $inicio = $data['de'] ? date('d/m/Y', strtotime($data['de'])) : '...';
                        $fim = $data['ate'] ? date('d/m/Y', strtotime($data['ate'])) : '...';

                        return "Vigência: {$inicio} até {$fim}";
                    }),

                Filter::make('data_limite_ago')
                    ->label('Data Limite AGO')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('de')
                            ->label('Data Limite Início AGO')
                            ->columnSpan(1),
                        DatePicker::make('ate')
                            ->label('Data Limite Final AGO')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['de'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('data_limite_ago', '>=', $date),
                            )
                            ->when(
                                $data['ate'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('data_limite_ago', '<=', $date),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (empty($data['de']) && empty($data['ate'])) {
                            return null;
                        }

                        $inicio = $data['de'] ? date('d/m/Y', strtotime($data['de'])) : '...';
                        $fim = $data['ate'] ? date('d/m/Y', strtotime($data['ate'])) : '...';

                        return "Data Limite AGO: {$inicio} até {$fim}";
                    }),

                Filter::make('mandato_fim')
                    ->label('Fim Mandato Síndico')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('de')
                            ->label('Fim Mandato Síndico Início')
                            ->columnSpan(1),
                        DatePicker::make('ate')
                            ->label('Fim Mandato Síndico Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['de'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('mandato_fim', '>=', $date),
                            )
                            ->when(
                                $data['ate'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('mandato_fim', '<=', $date),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (empty($data['de']) && empty($data['ate'])) {
                            return null;
                        }

                        $inicio = $data['de'] ? date('d/m/Y', strtotime($data['de'])) : '...';
                        $fim = $data['ate'] ? date('d/m/Y', strtotime($data['ate'])) : '...';

                        return "Fim Mandato Síndico: {$inicio} até {$fim}";
                    }),
            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('download')
                        ->label('Download Edital')
                        ->icon(Heroicon::ArrowDown)
                        ->url(fn($record) => route('issuer-assembleia.document.show', $record), true),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
