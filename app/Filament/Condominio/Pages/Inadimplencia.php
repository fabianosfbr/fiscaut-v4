<?php

namespace App\Filament\Condominio\Pages;

use App\Services\SuperlogicaConnectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use UnitEnum;

class Inadimplencia extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.condominio.pages.inadimplencia';

    protected static string|UnitEnum|null $navigationGroup = 'Cobranças';

    protected static ?string $title = 'Inadimplência';

    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('gerar_pdf')
                    ->label('PDF de Inadimplência')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function () {
                        $records = $this->fetchInadimplencias();

                        $filters = $this->tableFilters ?? [];
                        $search = $this->tableSearchQuery ?? null;

                        $records = $this->applyFilters($records, $filters);
                        $records = $this->applySearch($records, $search);

                        $pdf = Pdf::loadView('pdf.inadimplentes', [
                            'records' => $records,
                            'issuerName' => currentIssuer()->name ?? 'CONDOMÍNIO',
                            'idCondominio' => currentIssuer()->superlogica_condominio_id,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'inadimplentes-'.now()->format('d-m-Y').'.pdf');
                    }),
            ]),

        ];
    }

    public function table(Table $table)
    {
        return $table
            ->deferLoading()
            ->searchDebounce('750ms')
            ->records(function (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator {
                return $this->apiData($search, $sortColumn, $sortDirection, $page, $recordsPerPage);
            })
            ->columns([
                TextColumn::make('st_unidade_uni')
                    ->label('Unidade'),
                TextColumn::make('st_bloco_uni')
                    ->label('Bloco'),
                TextColumn::make('processo_judicial')
                    ->label('Situação')
                    ->state(fn (array $record): string => (string) (data_get($record, 'processo_judicial') ? 'Jurídico' : ''))
                    ->color(fn (array $record): string => (string) (data_get($record, 'processo_judicial') ? 'danger' : 'success'))
                    ->badge(),
                // \Filament\Tables\Columns\IconColumn::make('processo_judicial')
                //     ->label('Proc. Judicial')
                //     ->boolean(),
                TextColumn::make('st_sacado_uni')
                    ->label('Sacado')
                    ->description(fn (array $record): string => (string) (data_get($record, 'recebimento.0.contatosunidade.0.proprietario.0.cpf') ?? data_get($record, 'recebimento.0.contatosunidade.0.proprietario.0.cnpj') ?? ''))
                    ->searchable(),
                TextColumn::make('principal')
                    ->label('Principal')
                    ->state(fn (array $record): float => $this->sumRecebimentoValue($record, 'vl_emitido_recb'))
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
                TextColumn::make('juros')
                    ->label('Juros')
                    ->state(fn (array $record): float => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.juros'))
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
                TextColumn::make('multa')
                    ->label('Multa')
                    ->state(fn (array $record): float => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.multa'))
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
                TextColumn::make('atualiz')
                    ->label('Atualiz.')
                    ->state(fn (array $record): float => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.atualizacaomonetaria'))
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
                TextColumn::make('honorarios')
                    ->label('Honorários')
                    ->state(fn (array $record): float => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.honorarios'))
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->state(fn (array $record): float => $this->sumRecebimentoValue($record, 'encargos.0.valorcorrigido'))
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
            ])
            ->filters([

                Filter::make('data_emissao')
                    ->label('Data de Emissão')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('vencimento_de')
                            ->label('Vencimento Inicial')
                            ->columnSpan(1),
                        DatePicker::make('vencimento_ate')
                            ->label('Vencimento Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['vencimento_de'] ?? null) {
                            $indicators[] = Indicator::make('Vencimento a partir de '.Carbon::parse($data['vencimento_de'])->format('d/m/Y'))
                                ->removeField('vencimento_de');
                        }
                        if ($data['vencimento_ate'] ?? null) {
                            $indicators[] = Indicator::make('Vencimento até '.Carbon::parse($data['vencimento_ate'])->format('d/m/Y'))
                                ->removeField('vencimento_ate');
                        }

                        return $indicators;
                    }),

                Filter::make('atraso')
                    ->schema([
                        Select::make('dias')
                            ->label('Atraso')
                            ->options([
                                '10' => 'Mais de 10 dias',
                                '30' => 'Mais de 30 dias',
                                '60' => 'Mais de 60 dias',
                                '90' => 'Mais de 90 dias',
                            ]),
                    ])
                    ->indicateUsing(function (array $data): ?Indicator {
                        if (! ($data['dias'] ?? null)) {
                            return null;
                        }

                        return Indicator::make('Atraso: Mais de '.$data['dias'].' dias')
                            ->removeField('dias');
                    }),

                TernaryFilter::make('processo_judicial')
                    ->label('Processo Judicial')
                    ->placeholder('Todos')
                    ->trueLabel('Em processo')
                    ->falseLabel('Sem processo'),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                Action::make('detalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->url(function (array $record) {
                        $key = 'cobranca_detalhes_'.Str::uuid();
                        Cache::put($key, $record, now()->addMinutes(60));

                        return route('filament.condominio.pages.detalhes-cobranca', ['record_key' => $key]);
                    }),
            ]);
    }

    protected function apiData(?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator
    {
        $processoJudicial = $this->fetchProcessosJudiciais();
        $processoJudicialIds = $processoJudicial->pluck('id_unidade_uni')->toArray();

        $records = $this->fetchInadimplencias()->map(function ($record) use ($processoJudicialIds) {
            $record['processo_judicial'] = in_array(data_get($record, 'id_unidade_uni'), $processoJudicialIds);

            return $record;
        });

        $filters = $this->tableFilters ?? [];
        $records = $this->applyFilters($records, $filters);
        $records = $this->applySearch($records, $search);

        if ($sortColumn) {
            $records = $records->sortBy(function ($record) use ($sortColumn) {
                return match ($sortColumn) {
                    'principal' => $this->sumRecebimentoValue($record, 'vl_emitido_recb'),
                    'juros' => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.juros'),
                    'multa' => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.multa'),
                    'atualiz' => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.atualizacaomonetaria'),
                    'honorarios' => $this->sumRecebimentoValue($record, 'encargos.0.detalhes.honorarios'),
                    'total' => $this->sumRecebimentoValue($record, 'encargos.0.valorcorrigido'),
                    default => data_get($record, $sortColumn),
                };
            }, SORT_REGULAR, $sortDirection === 'desc');
        }

        $total = $records->count();
        $records = $records->forPage($page, $recordsPerPage);

        // dd($records);
        return new LengthAwarePaginator(
            $records,
            total: $total,
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    protected function fetchInadimplencias(): Collection
    {
        $issuer = currentIssuer();

        $service = new SuperlogicaConnectionService($issuer->tenant);

        $inadimplencias = $service
            ->receita()
            ->listarInadimplencia([
                'idCondominio' => $issuer->superlogica_condominio_id,
            ]);

        dd($issuer->superlogica_condominio_id, $inadimplencias);

        $records = collect($inadimplencias)->map(function ($record) {
            if (isset($record['recebimento']) && is_array($record['recebimento'])) {
                $recebimentos = collect($record['recebimento'])->sortBy(function ($recb) {
                    try {
                        return Carbon::createFromFormat('m/d/Y H:i:s', data_get($recb, 'dt_vencimento_recb'))->timestamp;
                    } catch (\Exception $e) {
                        try {
                            return Carbon::parse(data_get($recb, 'dt_vencimento_recb'))->timestamp;
                        } catch (\Exception $e) {
                            return 0;
                        }
                    }
                })->values()->all();

                $record['recebimento'] = $recebimentos;
            }

            return $record;
        });

        return $records;
    }

    protected function fetchProcessosJudiciais(): Collection
    {
        $issuer = currentIssuer();


        $service = new SuperlogicaConnectionService($issuer->tenant);

        $processosJudiciais = $service
            ->receita()
            ->listarProcessosJudiciais([
                'idCondominio' => $issuer->superlogica_condominio_id,
            ]);

        return collect($processosJudiciais);

    }

    protected function applyFilters(Collection $records, array $filters): Collection
    {
        $vencimentoDe = data_get($filters, 'vencimento.vencimento_de');
        $vencimentoAte = data_get($filters, 'vencimento.vencimento_ate');
        $atrasoDias = data_get($filters, 'atraso.dias');
        $processoJudicial = data_get($filters, 'processo_judicial.value');

        if ($processoJudicial !== null) {
            $records = $records->filter(function ($record) use ($processoJudicial) {
                return (bool) data_get($record, 'processo_judicial') === (bool) $processoJudicial;
            });
        }

        if (! $vencimentoDe && ! $vencimentoAte && ! $atrasoDias) {
            return $records;
        }

        return $records->map(function ($record) use ($vencimentoDe, $vencimentoAte, $atrasoDias) {
            if (! isset($record['recebimento']) || ! is_array($record['recebimento'])) {
                return $record;
            }

            $filteredRecebimentos = array_filter($record['recebimento'], function ($recb) use ($vencimentoDe, $vencimentoAte, $atrasoDias) {
                $keep = true;

                if ($vencimentoDe || $vencimentoAte) {
                    try {
                        $dtVencimento = Carbon::parse($recb['dt_vencimento_recb'])->startOfDay();

                        if ($vencimentoDe && $dtVencimento->lt(Carbon::parse($vencimentoDe)->startOfDay())) {
                            $keep = false;
                        }
                        if ($vencimentoAte && $dtVencimento->gt(Carbon::parse($vencimentoAte)->startOfDay())) {
                            $keep = false;
                        }
                    } catch (\Exception $e) {
                        // Ignorar parsing errors
                    }
                }

                if ($keep && $atrasoDias) {
                    $diasAtraso = (int) data_get($recb, 'encargos.0.diasatraso', 0);
                    if ($diasAtraso <= (int) $atrasoDias) {
                        $keep = false;
                    }
                }

                return $keep;
            });

            $record['recebimento'] = array_values($filteredRecebimentos);

            return $record;
        })->filter(function ($record) {
            return ! empty($record['recebimento']);
        });
    }

    protected function applySearch(Collection $records, ?string $search): Collection
    {
        if (! filled($search)) {
            return $records;
        }

        $search = (string) Str::of($search)->trim()->lower();

        return $records->filter(function (array $record) use ($search): bool {
            return Str::of((string) ($record['st_unidade_uni'] ?? ''))->lower()->contains($search)
                || Str::of((string) ($record['st_bloco_uni'] ?? ''))->lower()->contains($search)
                || Str::of((string) ($record['st_sacado_uni'] ?? ''))->lower()->contains($search);
        });
    }

    protected function sumRecebimentoValue(array $record, string $key): float
    {
        return collect($record['recebimento'] ?? [])->sum(fn ($recb) => (float) data_get($recb, $key, 0));
    }
}
