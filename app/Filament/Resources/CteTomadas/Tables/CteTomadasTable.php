<?php

namespace App\Filament\Resources\CteTomadas\Tables;

use App\Filament\Actions\ClassificarDocumentoAction;
use App\Filament\Actions\DownloadPdfCteAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Actions\DownloadXmlPdfCteEmLoteAction;
use App\Filament\Actions\RemoverClassificaoNfeAction;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Jobs\Sefaz\CheckNfeData;
use App\Models\ConhecimentoTransporteEletronico;
use App\Models\GeneralSetting;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CteTomadasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('data_emissao', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $issuer = Auth::user()->currentIssuer;
                $query->with('tagged')
                    ->with('apurada')
                    ->where('ctes.tomador_cnpj', $issuer->cnpj)
                    ->orderBy('ctes.data_emissao', 'DESC');
            })
            ->columns([
                TextColumn::make('nCTe')
                    ->label('Nº')
                    ->searchable()
                    ->sortable()
                    ->icon(function (ConhecimentoTransporteEletronico $record) {

                        if (is_null($record['metadata'])) {

                            return 'heroicon-o-document-text';
                        }

                        return null;
                    })
                    ->iconColor('success')
                    ->iconPosition('after')
                    ->tooltip(function (ConhecimentoTransporteEletronico $record) {

                        if (is_null($record['metadata'])) {

                            return 'Aguardando NFe';
                        }

                        return null;
                    }),

                TextColumn::make('serie')
                    ->label('Série')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('emitente_razao_social')
                    ->label('Emitente')
                    ->limit(30)
                    ->searchable(['emitente_nome', 'emitente_cnpj'])
                    ->size('sm')
                    ->description(function (ConhecimentoTransporteEletronico $record) {
                        return $record->emitente_cnpj;
                    }),

                TextColumn::make('cfop')
                    ->label('CFOP')
                    ->toggleable()
                    ->alignCenter(),

                ViewColumn::make('nfe_chave')
                    ->view('filament.tables.columns.view-cte-chave-nfe')
                    ->alignCenter()
                    ->label('Chave NFe'),

                IconColumn::make('apurada.status')
                    ->label('Apurada')
                    ->boolean()
                    ->default(false)
                    ->alignment(Alignment::Center)
                    ->toggleable(),

                TextColumn::make('vCTe')
                    ->label('Valor Total')
                    ->money('BRL'),

                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->toggleable(),

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

                TextColumn::make('status_cte')
                    ->label('Status')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('tpCTe')
                    ->label('Tipo')
                    ->alignCenter()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Normal',
                        '1' => 'Compl. de valor',
                        '2' => 'Anulação',
                        '3' => 'Substituição',
                    })
                    ->badge(),

                ViewChaveColumn::make('chave')
                    ->label('Chave Acesso')
                    ->tooltip('Chave Acesso do CT-e')
                    ->searchable()
                    ->alignCenter()
                    ->toggleable(),
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

                SelectFilter::make('status_cte')
                    ->label('Status CT-e')
                    ->options([
                        '100' => 'Ativa',
                        '101' => 'Cancelada',
                        '302' => 'Denegada',
                    ])
                    ->placeholder('Todos os status'),

                SelectFilter::make('tipo_cte')
                    ->label('Tipo')
                    ->options([
                        '0' => 'Normal',
                        '1' => 'Compl. de valor',
                        '2' => 'Anulação',
                        '3' => 'Substituição',
                    ]),

                TernaryFilter::make('escriturada')
                    ->label('Escriturada')
                    ->columnSpan(1)
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $data['value']
                            ? $query->whereHas('apurada', fn (Builder $query): Builder => $query->where('status', true))
                            : $query->where(function (Builder $query): Builder {
                                return $query
                                    ->whereDoesntHave('apurada')
                                    ->orWhereHas('apurada', fn (Builder $query): Builder => $query->where('status', false));
                            });
                    }),

                TernaryFilter::make('aguardando_nfe')
                    ->label('Aguardando NFe')
                    ->columnSpan(1)
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        return $data['value']
                            ? $query->whereNull('metadata')
                            : $query->whereNotNull('metadata');
                    }),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detalhes'),
                    ToggleEscrituracaoAction::make(),
                    ClassificarDocumentoAction::make(),
                    RemoverClassificaoNfeAction::make(),
                    DownloadXmlAction::make(),
                    DownloadPdfCteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('verificar-nfe')
                        ->label('Verificar NFe Associada')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                CheckNfeData::dispatch($record);
                            });
                        }),
                    DownloadXmlPdfCteEmLoteAction::make(),
                ]),
            ]);
    }
}
