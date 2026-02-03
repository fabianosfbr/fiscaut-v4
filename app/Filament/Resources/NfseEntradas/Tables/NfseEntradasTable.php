<?php

namespace App\Filament\Resources\NfseEntradas\Tables;

use App\Filament\Actions\ClassificarDocumentoAction;
use App\Filament\Actions\ClassificarDocumentoEmLoteAction;
use App\Filament\Actions\RemoverClassificaoNfeAction;
use App\Filament\Actions\ToggleEscrituacaoEmLoteAction;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Models\GeneralSetting;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NfseEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->distinct()
                    ->where('tomador_cnpj', Auth::user()->currentIssuer->cnpj);
            })
            ->defaultSort('data_emissao', 'desc')
            ->columns([
                TextColumn::make('numero')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prestador_servico')
                    ->label('Empresa')
                    ->limit(30)
                    ->searchable()
                    ->size('sm')
                    ->description(function (Model $record) {
                        return $record->prestador_cnpj;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),

                IconColumn::make('apurada.status')
                    ->label('Apurada')
                    ->boolean()
                    ->default(false)
                    ->alignment(Alignment::Center)
                    ->toggleable(),

                TextColumn::make('valor_servico')
                    ->label('Valor')
                    ->money('BRL'),

                TextColumn::make('data_entrada')
                    ->label('Entrada')
                    ->sortable()
                    ->toggleable()
                    ->date('d/m/Y'),

                TagBadgesColumn::make('tagged')
                    ->label('Etiqueta')
                    ->alignCenter()
                    ->emptyText('')
                    ->showTagCode(function () {
                        $issuerId = Auth::user()->currentIssuer->id;

                        return GeneralSetting::getValue(
                            name: 'configuracoes_gerais',
                            key: 'isNfeMostrarCodigoEtiqueta',
                            default: false,
                            issuerId: $issuerId
                        );
                    })
                    ->toggleable(),

                TextColumn::make('cancelada')
                    ->label('Status')
                    ->toggleable()
                    ->formatStateUsing(function ($record) {
                        return $record->cancelada ? 'Cancelada' : 'Ativa';
                    })
                    ->badge(),

                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->sortable()
                    ->toggleable()
                    ->date('d/m/Y'),
            ])
            ->filters([
                Filter::make('data_emissao')
                    ->label('Data de Emissão')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('data_emissao_inicio')
                            ->label('Data Emissão Início')
                            ->columnSpan(1),
                        DatePicker::make('data_emissao_fim')
                            ->label('Data Emissão Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['data_emissao_inicio']) && empty($data['data_emissao_fim'])) {
                            return null;
                        }

                        $inicio = $data['data_emissao_inicio'] ? date('d/m/Y', strtotime($data['data_emissao_inicio'])) : '...';
                        $fim = $data['data_emissao_fim'] ? date('d/m/Y', strtotime($data['data_emissao_fim'])) : '...';

                        return "Emissão: {$inicio} até {$fim}";
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['data_emissao_inicio'])) {
                            $query->whereDate('data_emissao', '>=', $data['data_emissao_inicio']);
                        }
                        if (! empty($data['data_emissao_fim'])) {
                            $query->whereDate('data_emissao', '<=', $data['data_emissao_fim']);
                        }

                        return $query;
                    }),

                Filter::make('data_entrada')
                    ->label('Data de Entrada')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('data_entrada_inicio')
                            ->label('Data Entrada Início')
                            ->columnSpan(1),
                        DatePicker::make('data_entrada_fim')
                            ->label('Data Entrada Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['data_entrada_inicio']) && empty($data['data_entrada_fim'])) {
                            return null;
                        }

                        $inicio = $data['data_entrada_inicio'] ? date('d/m/Y', strtotime($data['data_entrada_inicio'])) : '...';
                        $fim = $data['data_entrada_fim'] ? date('d/m/Y', strtotime($data['data_entrada_fim'])) : '...';

                        return "Entrada: {$inicio} até {$fim}";
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['data_entrada_inicio'])) {
                            $query->whereDate('data_entrada', '>=', $data['data_entrada_inicio']);
                        }
                        if (! empty($data['data_entrada_fim'])) {
                            $query->whereDate('data_entrada', '<=', $data['data_entrada_fim']);
                        }

                        return $query;
                    }),

                TernaryFilter::make('status_nota')
                    ->label('Nota Ativa')
                    ->columnSpan(1)
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $data['value']
                            ? $query->where('cancelada', false)
                            : $query->where('cancelada', true);
                    }),
            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detalhes'),
                    ToggleEscrituracaoAction::make(),
                    ClassificarDocumentoAction::make(),
                    RemoverClassificaoNfeAction::make(),

                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ToggleEscrituacaoEmLoteAction::make(),

                    ClassificarDocumentoEmLoteAction::make()
                        ->after(function () {

                            Notification::make()
                                ->title('Etiquetas aplicadas com sucesso')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
