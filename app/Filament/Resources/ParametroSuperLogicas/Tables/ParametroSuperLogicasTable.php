<?php

namespace App\Filament\Resources\ParametroSuperLogicas\Tables;

use App\Filament\Exports\ParametroSuperLogicaExporter;
use App\Models\HistoricoContabil;
use App\Models\PlanoDeConta;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ParametroSuperLogicasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', currentIssuer()->id);
            })
            ->columns([
                TextColumn::make('params')
                    ->label('Parametros')
                    ->badge()
                    ->color('gray')
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query->SearchByParametro(search: $search)),
                TextColumn::make('contaCredito')
                    ->label('Conta crédito')
                    ->formatStateUsing(function ($state) {
                        return $state?->codigo;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return $state?->codigo . ' | ' . $state?->nome;
                    })
                    ->badge(),
                TextColumn::make('contaDebito')
                    ->label('Conta débito')
                    ->formatStateUsing(function ($state) {
                        return $state?->codigo;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return $state?->codigo . ' | ' . $state?->nome;
                    })
                    ->badge(),
                TextColumn::make('codigo_historico')
                    ->label('Cód. Histórico')
                    ->badge(),
                IconColumn::make('check_value')
                    ->label('Verificar valor')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('conta_credito')
                    ->label('Conta crédito')
                    ->searchable()
                    ->options(function () {
                        return Cache::remember('planos_contas_credito_super_logica_' . currentIssuer()->id, 360, function () {
                            return PlanoDeConta::where('issuer_id', currentIssuer()->id)
                                ->get()
                                ->keyBy('id')
                                ->map(fn($plano) => $plano->codigo . ' - ' . $plano->nome)
                                ->toArray();
                        });
                    })
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['value'], fn($q, $value) => $q->where('conta_credito', $value));
                    }),
                SelectFilter::make('conta_debito')
                    ->label('Conta débito')
                    ->searchable()
                    ->options(function () {
                        return Cache::remember('planos_contas_debito_super_logica_' . currentIssuer()->id, 360, function () {
                            return PlanoDeConta::where('issuer_id', currentIssuer()->id)
                                ->get()
                                ->keyBy('id')
                                ->map(fn($plano) => $plano->codigo . ' - ' . $plano->nome)
                                ->toArray();
                        });
                    })
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['value'], fn($q, $value) => $q->where('conta_debito', $value));
                    }),
                Filter::make('codigo_historico')
                    ->form([
                        Select::make('codigo_historico')
                            ->label('Histórico')
                            ->searchable()
                            ->options(function () {
                                return Cache::remember('historicos_super_logica_' . currentIssuer()->id, 360, function () {
                                    return HistoricoContabil::where('issuer_id', currentIssuer()->id)
                                        ->get()
                                        ->keyBy('id')
                                        ->map(fn($item) => $item->codigo . ' - ' . $item->descricao)
                                        ->toArray();
                                });
                            }),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['codigo_historico'], function ($q) use ($data) {
                            $historico = HistoricoContabil::find($data['codigo_historico']);

                            $q->where('codigo_historico', $historico->codigo);
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['codigo_historico']) {
                            return null;
                        }

                        $historico = HistoricoContabil::find($data['codigo_historico']);

                        return 'Histórico: ' . $historico->codigo . ' | ' . $historico->descricao;
                    }),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Exportar dados')
                        ->modalHeading('Exportar dados')
                        ->modalDescription('Exportar os registros selecionados para formato .xlsx e .csv')
                        ->modalWidth('md')
                        ->exporter(ParametroSuperLogicaExporter::class)
                        ->columnMapping(false)
                        ->color('primary'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
