<?php

namespace App\Filament\Resources\ParametroGerals\Tables;

use App\Models\HistoricoContabil;
use App\Models\PlanoDeConta;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ParametroGeralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('params')
                    ->label('Parametros')
                    ->badge()
                    ->color('gray')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->SearchByParametro(search: $search)),

                TextColumn::make('plano_de_conta')
                    ->label('Conta Contábil')
                    ->limit(30)
                    ->formatStateUsing(function ($state) {
                        return $state->codigo.' | '.$state->nome;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state?->codigo.' | '.$state?->nome) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state?->codigo.' | '.$state?->nome;
                    })
                    ->color('gray')
                    ->badge(),
                TextColumn::make('codigo_historico')
                    ->label('Cód. Histórico')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('conta_contabil')
                    ->label('Conta Contábil')
                    ->searchable()
                    ->options(
                        function () {
                            $planos = Cache::remember('planos_contas_'.currentIssuer()->id, 360, function () {
                                return PlanoDeConta::where('issuer_id', currentIssuer()->id)->where('tipo', '=', 'A')
                                    ->get()
                                    ->keyBy('id')
                                    ->map(fn ($plano) => $plano->codigo.' - '.$plano->nome)
                                    ->toArray();
                            });

                            return $planos;
                        }
                    )->columnSpan(2),

                Filter::make('codigo_historico')
                    ->form([
                        Select::make('codigo_historico')
                            ->label('Histórico')
                            ->searchable()
                            ->options(function () {

                                $historico = Cache::remember('historicos_'.currentIssuer()->id, 360, function () {
                                    return HistoricoContabil::where('issuer_id', currentIssuer()->id)
                                        ->get()
                                        ->keyBy('id')
                                        ->map(fn ($item) => $item->codigo.' - '.$item->descricao)
                                        ->toArray();
                                });

                                return $historico;
                            }),
                    ])->query(function (Builder $query, array $data) {

                        return $query->when($data['codigo_historico'], function ($q) use ($data) {
                            $historico = HistoricoContabil::find($data['codigo_historico']);

                            $q->where('codigo_historico', $historico->codigo);
                        });
                    })->indicateUsing(function (array $data): ?string {

                        if (! $data['codigo_historico']) {
                            return null;
                        }

                        $historico = HistoricoContabil::find($data['codigo_historico']);

                        return 'Histórico: '.$historico->codigo.' | '.$historico->descricao;
                    })
                    ->columnSpan(2),

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
