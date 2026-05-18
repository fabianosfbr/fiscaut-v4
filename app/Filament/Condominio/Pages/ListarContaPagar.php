<?php

namespace App\Filament\Condominio\Pages;

use App\Services\SuperlogicaConnectionService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use UnitEnum;

class ListarContaPagar extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.condominio.pages.listar-conta-pagar';

    protected static string|UnitEnum|null $navigationGroup = 'Cobranças';

    protected static ?string $title = 'Contas a Pagar';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->searchDebounce('750ms')
            ->records(function (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator {
                return $this->apiData($search, $sortColumn, $sortDirection, $page, $recordsPerPage);
            })
            ->columns([
                TextColumn::make('id_despesa_des')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('dt_vencimento_pdes')
                    ->label('Vencimento')
                    ->state(fn(array $record): string => $this->formatDate(data_get($record, 'dt_vencimento_pdes')))
                    ->sortable(),
                TextColumn::make('st_descricao_cont')
                    ->label('Descrição')
                    ->state(function (array $record): string {
                        return data_get($record, 'apropriacao.0.st_descricao_cont', '') . ' ' . data_get($record, 'apropriacao.0.st_complemento_apro', '');
                    })
                    ->description(function (array $record) {
                        ds($record);
                        return new HtmlString('<span style="color: #b3b3b6ff;">Despesa </span>' . $record['id_parcela_pdes'] . '<span style="color: #b3b3b6ff;"> para o favorecido </span>' . $record['st_nomerecebedor_fav'] ?? '');
                    })
                    ->searchable(['st_descricao_cont', 'st_nomerecebedor_fav'])
                    ->sortable(),
                TextColumn::make('forma_pagamento_text')
                    ->label('Forma de Pagamento')
                    ->state(fn(array $record): string => $this->mapFormaPagamento(data_get($record, 'id_forma_pag')))
                    ->badge()
                    ->sortable(),
                TextColumn::make('st_descricao_cb')
                    ->label('Conta Bancária')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('vl_valor_pdes')
                    ->label('Valor')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('data_despesa')
                    ->label('Data de Vencimento')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('despesa_de')
                            ->label('Data Inicial')
                            ->columnSpan(1),
                        DatePicker::make('despesa_ate')
                            ->label('Data Final')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['despesa_de'] ?? null) {
                            $indicators[] = Indicator::make('Data a partir de ' . Carbon::parse($data['despesa_de'])->format('d/m/Y'))
                                ->removeField('despesa_de');
                        }
                        if ($data['despesa_ate'] ?? null) {
                            $indicators[] = Indicator::make('Data até ' . Carbon::parse($data['despesa_ate'])->format('d/m/Y'))
                                ->removeField('despesa_ate');
                        }

                        return $indicators;
                    }),
                SelectFilter::make('id_forma_pag')
                    ->label('Forma de Pagamento')
                    ->options($this->getFormaPagamentoOptions()),
                Filter::make('st_nomerecebedor_fav')
                    ->label('Favorecido')
                    ->schema([
                        TextInput::make('favorecido')
                            ->label('Nome do Favorecido')
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['favorecido'] ?? null) {
                            $query->where('st_nomerecebedor_fav', 'like', '%' . $data['favorecido'] . '%');
                        }

                        return $query;
                    }),
                Filter::make('st_descricao_cont')
                    ->label('Descrição')
                    ->schema([
                        TextInput::make('descricao')
                            ->label('Texto da Descrição')
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['descricao'] ?? null) {
                            $query->where('st_descricao_cont', 'like', '%' . $data['descricao'] . '%');
                        }

                        return $query;
                    }),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordUrl(null);
    }

    protected function apiData(?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator
    {
        $records = $this->fetchContasPagar();

        $filters = $this->tableFilters ?? [];
        $records = $this->applyFilters($records, $filters);
        $records = $this->applySearch($records, $search);

        if ($sortColumn) {
            $records = $records->sortBy(function ($record) use ($sortColumn) {
                return match ($sortColumn) {
                    'vl_valor_pdes' => (float) data_get($record, 'vl_valor_pdes', 0),
                    'dt_vencimento_pdes' => $this->parseDateToTimestamp(data_get($record, 'dt_vencimento_pdes')),
                    'forma_pagamento_text' => $this->mapFormaPagamento(data_get($record, 'id_forma_pag')),
                    'st_descricao_cont' => $this->getDescricaoApropriacao($record),
                    default => data_get($record, $sortColumn) ?? '',
                };
            }, SORT_REGULAR, $sortDirection === 'desc');
        } else {
            $records = $records->sortBy(function ($record) {
                return $this->parseDateToTimestamp(data_get($record, 'dt_vencimento_pdes'));
            });
        }

        $total = $records->count();
        $records = $records->forPage($page, $recordsPerPage)->values();

        return new LengthAwarePaginator(
            $records,
            total: $total,
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    protected function fetchContasPagar(): Collection
    {
        $issuer = currentIssuer();

        $service = new SuperlogicaConnectionService($issuer->tenant);

        $despesas = $service
            ->despesa()
            ->listarDespesa([
                'idCondominio' => $issuer->superlogica_condominio_id,
                'dtInicio' => '01/01/2026',
                'comStatus' => 'pendentes',
            ]);

        return collect($despesas);
    }

    protected function applyFilters(Collection $records, array $filters): Collection
    {
        $despesaDe = data_get($filters, 'data_despesa.despesa_de');
        $despesaAte = data_get($filters, 'data_despesa.despesa_ate');
        $formaPagamento = data_get($filters, 'id_forma_pag.value');
        $favorecido = data_get($filters, 'st_nomerecebedor_fav.favorecido');
        $descricao = data_get($filters, 'st_descricao_cont.descricao');

        if (!empty($formaPagamento)) {
            $records = $records->filter(function ($record) use ($formaPagamento) {
                $idFormaPag = data_get($record, 'id_forma_pag');
                $idFormaPagStr = is_array($idFormaPag) ? (string) array_first($idFormaPag) : (string) $idFormaPag;

                return $idFormaPagStr === (string) $formaPagamento;
            });
        }

        if ($favorecido) {
            $favorecido = (string) Str::of($favorecido)->trim()->lower();
            $records = $records->filter(function ($record) use ($favorecido) {
                return Str::of((string) (data_get($record, 'st_nomerecebedor_fav') ?? ''))->lower()->contains($favorecido);
            });
        }

        if ($descricao) {
            $descricao = (string) Str::of($descricao)->trim()->lower();
            $records = $records->filter(function ($record) use ($descricao) {
                $apropriacaoDesc = $this->getDescricaoApropriacao($record);

                return Str::of((string) $apropriacaoDesc)->lower()->contains($descricao);
            });
        }

        if (!$despesaDe && !$despesaAte) {
            return $records;
        }

        return $records->filter(function ($record) use ($despesaDe, $despesaAte) {
            try {
                $dtVencimento = Carbon::parse(data_get($record, 'dt_vencimento_pdes'))->startOfDay();

                if ($despesaDe && $dtVencimento->lt(Carbon::parse($despesaDe)->startOfDay())) {
                    return false;
                }
                if ($despesaAte && $dtVencimento->gt(Carbon::parse($despesaAte)->startOfDay())) {
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
        if (!filled($search)) {
            return $records;
        }

        $search = (string) Str::of($search)->trim()->lower();

        return $records->filter(function (array $record) use ($search): bool {
            return Str::of((string) (data_get($record, 'id_despesa_des') ?? ''))->lower()->contains($search) ||
                Str::of((string) (data_get($record, 'st_descricao_cb') ?? ''))->lower()->contains($search) ||
                Str::of((string) ($this->getDescricaoApropriacao($record) ?? ''))->lower()->contains($search) ||
                Str::of((string) (data_get($record, 'st_nomerecebedor_fav') ?? ''))->lower()->contains($search) ||
                Str::of((string) $this->mapFormaPagamento(data_get($record, 'id_forma_pag')))->lower()->contains($search) ||
                Str::of((string) (data_get($record, 'vl_valor_pdes') ?? ''))->lower()->contains($search);
        });
    }

    protected function getDescricaoApropriacao(array $record): string
    {
        $apropriacao = data_get($record, 'apropriacao');

        if (!is_array($apropriacao) || empty($apropriacao)) {
            return '-';
        }

        $primeira = array_values($apropriacao)[0] ?? null;

        if (!$primeira) {
            return '-';
        }

        return data_get($primeira, 'st_descricao_cont', '-');
    }

    protected function mapFormaPagamento(mixed $id): string
    {
        $map = $this->getFormaPagamentoMap();

        return $map[(string) ($id ?? '')] ?? 'Indefinido';
    }

    protected function getFormaPagamentoOptions(): array
    {
        return array_filter($this->getFormaPagamentoMap(), function ($key) {
            return $key !== '';
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function getFormaPagamentoMap(): array
    {
        return [
            '' => 'Indefinido',
            '0' => 'Boleto',
            '1' => 'Cheque',
            '2' => 'Dinheiro',
            '3' => 'Cartão de Crédito',
            '4' => 'Cartão de Débito',
            '7' => 'Débito Automático',
            '8' => 'Trans. Bancária',
            '9' => 'Doc/Ted',
            '10' => 'Outros',
            '12' => 'Pix',
            '13' => 'DCTFWeb',
            '14' => 'Débito Automático',
        ];
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
