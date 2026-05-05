<?php

namespace App\Filament\Resources\NfceSaidas\Tables;

use App\Enums\StatusNfeEnum;
use App\Filament\Actions\DownloadXmlPdfNfceEmLoteAction;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Models\NotaFiscalConsumidor;
use App\Services\Xml\XmlReaderService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use NFePHP\DA\NFe\Danfce;

class NfceSaidasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->where('emitente_cnpj', currentIssuer()->cnpj);
            })
            ->defaultSort('data_emissao', 'desc')
            ->paginated([10, 25, 50, 100])
            ->recordUrl(null)
            ->searchDebounce('750ms')
            ->columns([
                TextColumn::make('nNF')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('emitente_razao_social')
                    ->label('Empresa')
                    ->limit(50)
                    ->searchable()
                    ->size('sm')
                    ->description(function (NotaFiscalConsumidor $record) {
                        // $data = (new XmlReaderService)->read($record->xml);
                        // dd($data);
                        return $record->emitente_cnpj;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),

                TextColumn::make('vNfe')
                    ->label('Valor da Nota')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('vNfe', str_replace(',', '.', $search));
                    })
                    ->money('BRL'),

                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->sortable()
                    ->toggleable()
                    ->date('d/m/Y'),

                TextColumn::make('status_nota')
                    ->label('Status')
                    ->toggleable()
                    ->badge(),

                ViewChaveColumn::make('chave')
                    ->label('Chave')
                    ->searchable()
                    ->alignCenter(),
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

                SelectFilter::make('status_nota')
                    ->label('Status')
                    ->options(StatusNfeEnum::class)
                    ->columnSpan(1),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    Action::make('detalhes')
                        ->label('Detalhes')
                        ->color('primary')
                        ->icon(Heroicon::Eye)
                        ->url(function (NotaFiscalConsumidor $record) {
                            $data = (new XmlReaderService)->read(gzuncompress($record->xml));
                            $urlQrcode = $data['nfeProc']['NFe']['infNFeSupl']['qrCode'] ?? null;

                            return $urlQrcode;
                        }, true),

                    Action::make('download-xml')
                        ->label('Download XML')
                        ->icon(Heroicon::DocumentArrowDown)
                        ->color('primary')
                        ->action(function ($record) {
                            $name = $record->chave;

                            return response()->streamDownload(function () use ($record) {
                                echo gzuncompress($record->xml);
                            }, $name.'.xml');
                        }),

                    Action::make('download-pdf')
                        ->label('Download PDF')
                        ->color('primary')
                        ->icon(Heroicon::DocumentArrowDown)
                        ->action(function ($record) {
                            $name = $record->chave;
                            $pdf = null;
                            try {
                                // Gera o Danfce
                                $danfe = new Danfce(gzuncompress($record->xml));
                                $danfe->creditsIntegratorFooter(env('APP_FOOTER_CREDITS_DANFE'), false);
                                $pdf = $danfe->render();
                            } catch (\Exception $e) {
                            }

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf;
                            }, $name.'.pdf');
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DownloadXmlPdfNfceEmLoteAction::make(),
                ]),
            ]);
    }
}
