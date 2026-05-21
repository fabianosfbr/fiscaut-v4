<?php

namespace App\Filament\Condominio\Pages;

use App\Services\SuperlogicaConnectionService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class ListarCobranca extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.condominio.pages.listar-cobranca';

    protected static string|UnitEnum|null $navigationGroup = 'Cobranças';

    protected static ?string $title = 'Listar Cobrança';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->searchDebounce('750ms')
            ->records(function (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator {
                return $this->apiData($search, $sortColumn, $sortDirection, $page, $recordsPerPage);
            })

            ->columns([
                TextColumn::make('st_unidade_uni')
                    ->label('Unidade')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('st_bloco_uni')
                    ->label('Bloco')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('st_sacado_uni')
                    ->label('Sacado')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('st_documento_recb')
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dt_vencimento_recb')
                    ->label('Vencimento')
                    ->state(fn (array $record): string => $this->formatDate(data_get($record, 'dt_vencimento_recb')))
                    ->sortable(),

                TextColumn::make('dt_recebimento_recb')
                    ->label('Recebimento')
                    ->state(fn (array $record): string => $this->formatDate(data_get($record, 'dt_recebimento_recb')))
                    ->sortable(),

                TextColumn::make('fl_status_recb')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Pendente',
                        '3' => 'Liquidada',
                        default => 'Indefinido',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'warning',
                        '3' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('vl_total_recb')
                    ->label('Valor Total')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('data_vencimento')
                    ->label('Data de Vencimento')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('vencimento_de')
                            ->label('Vencimento Inicial')
                            ->columnSpan(1),
                        DatePicker::make('vencimento_ate')
                            ->label('Vencimento Final')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
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

                SelectFilter::make('fl_status_recb')
                    ->label('Status')
                    ->options([
                        '0' => 'Pendente',
                        '3' => 'Liquidada',
                    ])
                    ->default('0')
                    ->multiple(),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                Action::make('visualizar_segundavia')
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (array $record): ?string => data_get($record, 'link_segundavia', null))
                    ->openUrlInNewTab(),
            ])
            ->recordUrl(null);
    }

    protected function apiData(?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator
    {
        $records = $this->fetchListarCobranca();

        $filters = $this->tableFilters ?? [];
        $records = $this->applyFilters($records, $filters);
        $records = $this->applySearch($records, $search);

        if ($sortColumn) {
            $records = $records->sortBy(function ($record) use ($sortColumn) {
                return match ($sortColumn) {
                    'vl_total_recb' => (float) data_get($record, 'vl_total_recb', 0),
                    'vl_emitido_recb' => (float) data_get($record, 'vl_emitido_recb', 0),
                    'dt_vencimento_recb' => $this->parseDateToTimestamp(data_get($record, 'dt_vencimento_recb')),
                    'dt_recebimento_recb' => $this->parseDateToTimestamp(data_get($record, 'dt_recebimento_recb')),
                    default => data_get($record, $sortColumn),
                };
            }, SORT_REGULAR, $sortDirection === 'desc');
        } else {
            $records = $records->sortBy(function ($record) {
                return match (data_get($record, 'fl_status_recb')) {
                    '0' => 0,   // Pendente
                    '3' => 1,   // Liquidada
                    default => 2,   // Indefinido
                };
            }, SORT_REGULAR, false);
        }

        $total = $records->count();
        $records = $records->forPage($page, $recordsPerPage);

        return new LengthAwarePaginator(
            $records,
            total: $total,
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    protected function fetchListarCobranca(): Collection
    {
        $issuer = currentIssuer();

        $service = new SuperlogicaConnectionService($issuer->tenant);

        $cobrancas = $service
            ->receita()
            ->listarCobranca([
                'idCondominio' => $issuer->superlogica_condominio_id,
                'status' => 'validos',
                //  'dtInicio' => '10/01/2020',
                'comContatosDaUnidade' => 1,
            ]);

        $unidades = $this->fetchUnidadesCache($service, $issuer->superlogica_condominio_id);

        return collect($cobrancas)->map(function ($cobranca) use ($unidades) {
            $cobranca = array_merge($cobranca, $this->extractUnidadeData($cobranca));

            $idUnidade = data_get($cobranca, 'id_unidade_uni');
            if ($idUnidade && $unidade = $unidades->get($idUnidade)) {
                if (empty($cobranca['st_unidade_uni'])) {
                    $cobranca['st_unidade_uni'] = data_get($unidade, 'st_unidade_uni');
                }
                if (empty($cobranca['st_bloco_uni'])) {
                    $cobranca['st_bloco_uni'] = data_get($unidade, 'st_bloco_uni');
                }
            }

            return $cobranca;
        });
    }

    protected function fetchUnidadesCache(SuperlogicaConnectionService $service, int|string $idCondominio): Collection
    {
        $sessionKey = 'superlogica_unidades_'.$idCondominio;

        if (session()->has($sessionKey)) {
            return collect(session($sessionKey))->keyBy('id_unidade_uni');
        }

        $allUnidades = collect();
        $pagina = 1;

        while (true) {
            try {
                $unidades = $service->unidade()->listar([
                    'idCondominio' => $idCondominio,
                    'exibirDadosDosContatos' => 1,
                    'exibirGruposDasUnidades' => 1,
                    'itensPorPagina' => 50,
                    'pagina' => $pagina,
                ]);
            } catch (\Throwable $e) {
                break;
            }

            $unidades = collect($unidades);

            if ($unidades->isEmpty()) {
                break;
            }

            $allUnidades = $allUnidades->merge($unidades);
            $pagina++;

            if ($pagina > 100) {
                break;
            }
        }

        $mapped = $allUnidades->map(fn ($u) => [
            'id_unidade_uni' => data_get($u, 'id_unidade_uni'),
            'st_unidade_uni' => data_get($u, 'st_unidade_uni'),
            'st_bloco_uni' => data_get($u, 'st_bloco_uni'),
            'st_sacado_uni' => data_get($u, 'st_sacado_uni'),
        ]);

        session([$sessionKey => $mapped->values()->all()]);

        return $mapped->keyBy('id_unidade_uni');
    }

    protected function extractUnidadeData(array $cobranca): array
    {
        $result = [
            'st_unidade_uni' => null,
            'st_bloco_uni' => null,
            'st_sacado_uni' => null,
        ];

        if (isset($cobranca['st_unidade_uni'])) {
            $result['st_unidade_uni'] = $cobranca['st_unidade_uni'];
        }
        if (isset($cobranca['st_bloco_uni'])) {
            $result['st_bloco_uni'] = $cobranca['st_bloco_uni'];
        }
        if (isset($cobranca['st_sacado_uni'])) {
            $result['st_sacado_uni'] = $cobranca['st_sacado_uni'];
        }

        if (! empty(array_filter($result, fn ($v) => ! is_null($v)))) {
            return $result;
        }

        if (! empty($cobranca['sacado_formatado'])) {
            $result['st_sacado_uni'] = (string) $cobranca['sacado_formatado'];
        } elseif (! empty($cobranca['nome_formatado'])) {
            $result['st_sacado_uni'] = (string) $cobranca['nome_formatado'];
        } elseif (! empty($cobranca['proprietario_nome'])) {
            $result['st_sacado_uni'] = (string) $cobranca['proprietario_nome'];
        }

        if (! empty($cobranca['contatosunidade'])) {
            $contato = is_array($cobranca['contatosunidade']) ? array_values($cobranca['contatosunidade'])[0] ?? null : null;

            if ($contato) {
                $result['st_unidade_uni'] = $result['st_unidade_uni'] ?? ($contato['st_unidade_uni'] ?? null);
                $result['st_bloco_uni'] = $result['st_bloco_uni'] ?? ($contato['st_bloco_uni'] ?? null);
                $result['st_sacado_uni'] = $result['st_sacado_uni'] ?? ($contato['morador'] ?? data_get($contato, 'proprietario.0.nome') ?? null);
            }

            return $result;
        }

        if (! empty($cobranca['unidade'])) {
            $unidade = is_array($cobranca['unidade']) ? array_values($cobranca['unidade'])[0] ?? null : null;

            if ($unidade) {
                $result['st_unidade_uni'] = $result['st_unidade_uni'] ?? ($unidade['st_unidade_uni'] ?? null);
                $result['st_bloco_uni'] = $result['st_bloco_uni'] ?? ($unidade['st_bloco_uni'] ?? null);
                $result['st_sacado_uni'] = $result['st_sacado_uni'] ?? ($unidade['st_sacado_uni'] ?? null);
            }

            return $result;
        }

        return $result;
    }

    protected function applyFilters(Collection $records, array $filters): Collection
    {
        $vencimentoDe = data_get($filters, 'data_vencimento.vencimento_de');
        $vencimentoAte = data_get($filters, 'data_vencimento.vencimento_ate');
        $status = data_get($filters, 'fl_status_recb.values', []);

        if (! empty($status)) {
            $records = $records->filter(function ($record) use ($status) {
                return in_array((string) data_get($record, 'fl_status_recb'), $status, true);
            });
        }

        if (! $vencimentoDe && ! $vencimentoAte) {
            return $records;
        }

        return $records->filter(function ($record) use ($vencimentoDe, $vencimentoAte) {
            try {
                $dtVencimento = Carbon::parse(data_get($record, 'dt_vencimento_recb'))->startOfDay();

                if ($vencimentoDe && $dtVencimento->lt(Carbon::parse($vencimentoDe)->startOfDay())) {
                    return false;
                }
                if ($vencimentoAte && $dtVencimento->gt(Carbon::parse($vencimentoAte)->startOfDay())) {
                    return false;
                }

                return true;
            } catch (\Exception $e) {
                return true;
            }
        });
    }

    protected function applySearch(Collection $records, ?string $search): Collection
    {
        if (! filled($search)) {
            return $records;
        }

        $search = (string) Str::of($search)->trim()->lower();

        return $records->filter(function (array $record) use ($search): bool {
            return Str::of((string) (data_get($record, 'st_documento_recb') ?? ''))->lower()->contains($search)
                || Str::of((string) (data_get($record, 'ar_nomeformas_calc') ?? ''))->lower()->contains($search)
                || Str::of((string) (data_get($record, 'dt_vencimento_recb') ?? ''))->lower()->contains($search)
                || Str::of((string) (data_get($record, 'dt_recebimento_recb') ?? ''))->lower()->contains($search)
                || Str::of((string) (data_get($record, 'st_unidade_uni') ?? ''))->lower()->contains($search)
                || Str::of((string) (data_get($record, 'st_bloco_uni') ?? ''))->lower()->contains($search)
                || Str::of((string) (data_get($record, 'st_sacado_uni') ?? ''))->lower()->contains($search);
        });
    }

    protected function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    protected function parseDateToTimestamp(?string $date): int
    {
        if (empty($date)) {
            return 0;
        }

        try {
            return Carbon::parse($date)->timestamp;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
