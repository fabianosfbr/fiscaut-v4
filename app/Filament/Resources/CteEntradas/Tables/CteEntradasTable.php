<?php

namespace App\Filament\Resources\CteEntradas\Tables;

use App\Filament\Actions\DownloadPdfCteAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Actions\DownloadXmlPdfCteEmLoteAction;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Models\ConhecimentoTransporteEletronico;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CteEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('data_emissao', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $issuer = Auth::user()->currentIssuer;
                $query->where('destinatario_cnpj', $issuer->cnpj);
            })
            ->columns([
                TextColumn::make('nCTe')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('vCTe')
                    ->label('Valor Total')
                    ->money('BRL'),

                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->date('d/m/Y')
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
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detalhes'),
                    DownloadXmlAction::make(),
                    DownloadPdfCteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DownloadXmlPdfCteEmLoteAction::make(),
                ]),
            ]);
    }
}
