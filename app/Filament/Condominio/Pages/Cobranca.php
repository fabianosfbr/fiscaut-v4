<?php

namespace App\Filament\Condominio\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use BackedEnum;

class Cobranca extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.condominio.pages.cobranca';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $title = 'Cobranças';

    public function table(Table $table)
    {
        return $table
            ->deferLoading()
            ->records(function (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator {
                return $this->apiData($search, $page, $recordsPerPage);
            })
            ->columns([
                TextColumn::make('st_unidade_uni')
                    ->label('Unidade'),
                TextColumn::make('st_bloco_uni')
                    ->label('Bloco'),
                TextColumn::make('st_sacado_uni')
                    ->label('Sacado')
                    ->searchable(),
                TextColumn::make('principal')
                    ->label('Principal')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn($recb) => (float) data_get($recb, 'vl_emitido_recb', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('juros')
                    ->label('Juros')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn($recb) => (float) data_get($recb, 'encargos.0.detalhes.juros', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('multa')
                    ->label('Multa')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn($recb) => (float) data_get($recb, 'encargos.0.detalhes.multa', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('atualiz')
                    ->label('Atualiz.')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn($recb) => (float) data_get($recb, 'encargos.0.detalhes.atualizacaomonetaria', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('honorarios')
                    ->label('Honorários')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn($recb) => (float) data_get($recb, 'encargos.0.detalhes.honorarios', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('total')
                    ->label('Total')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn($recb) => (float) data_get($recb, 'encargos.0.valorcorrigido', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
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
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('Vencimento a partir de ' . \Illuminate\Support\Carbon::parse($data['vencimento_de'])->format('d/m/Y'))
                                ->removeField('vencimento_de');
                        }
                        if ($data['vencimento_ate'] ?? null) {
                            $indicators[] = \Filament\Tables\Filters\Indicator::make('Vencimento até ' . \Illuminate\Support\Carbon::parse($data['vencimento_ate'])->format('d/m/Y'))
                                ->removeField('vencimento_ate');
                        }
                        return $indicators;
                    }),

                Filter::make('atraso')
                    ->schema([
                        \Filament\Forms\Components\Select::make('dias')
                            ->label('Atraso')
                            ->options([
                                '10' => 'Mais de 10 dias',
                                '30' => 'Mais de 30 dias',
                                '60' => 'Mais de 60 dias',
                                '90' => 'Mais de 90 dias',
                            ])
                    ])
                    ->indicateUsing(function (array $data): ?\Filament\Tables\Filters\Indicator {
                        if (!($data['dias'] ?? null)) {
                            return null;
                        }
                        return \Filament\Tables\Filters\Indicator::make('Atraso: Mais de ' . $data['dias'] . ' dias')
                            ->removeField('dias');
                    })
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->actions([
                Action::make('detalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalhes da Cobrança')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalContent(fn(array $record) => view('filament.condominio.pages.cobranca-detalhes', ['recebimentos' => $record['recebimento'] ?? []]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
            ]);
    }

    protected function apiData(?string $search, int $page, int|string $recordsPerPage): LengthAwarePaginator
    {
        $issuer = currentIssuer();
        $service = new \App\Services\SuperlogicaConnectionService($issuer);

        $inadimplencias = $service
            ->receita()
            ->listarInadimplencia([
                'idCondominio' => $issuer->superlogica_condominio_id,
                'posicaoEm' => now()->format('m/d/Y'),
                'comValoresAtualizadosPorComposicao' => 1,
                'apenasResumoInad' => 0,
                'comDadosDaReceita' => 1,
                'semAcordo' => 1,
                'semProcesso' => 1,
            ]);

        $records = collect($inadimplencias);

        $filters = $this->tableFilters ?? [];
        $vencimentoDe = data_get($filters, 'vencimento.vencimento_de');
        $vencimentoAte = data_get($filters, 'vencimento.vencimento_ate');
        $atrasoDias = data_get($filters, 'atraso.dias');

        if ($vencimentoDe || $vencimentoAte || $atrasoDias) {
            $records = $records->map(function ($record) use ($vencimentoDe, $vencimentoAte, $atrasoDias) {
                if (!isset($record['recebimento']) || !is_array($record['recebimento'])) {
                    return $record;
                }

                $filteredRecebimentos = array_filter($record['recebimento'], function ($recb) use ($vencimentoDe, $vencimentoAte, $atrasoDias) {
                    $keep = true;

                    if ($vencimentoDe || $vencimentoAte) {
                        try {
                            $dtVencimento = \Illuminate\Support\Carbon::parse($recb['dt_vencimento_recb'])->startOfDay();

                            if ($vencimentoDe && $dtVencimento->lt(\Illuminate\Support\Carbon::parse($vencimentoDe)->startOfDay())) {
                                $keep = false;
                            }
                            if ($vencimentoAte && $dtVencimento->gt(\Illuminate\Support\Carbon::parse($vencimentoAte)->startOfDay())) {
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
                return !empty($record['recebimento']);
            });
        }

        if (filled($search)) {
            $search = (string) Str::of($search)->trim()->lower();
            $records = $records->filter(function (array $record) use ($search): bool {
                return Str::of((string) ($record['st_unidade_uni'] ?? ''))->lower()->contains($search)
                    || Str::of((string) ($record['st_bloco_uni'] ?? ''))->lower()->contains($search)
                    || Str::of((string) ($record['st_sacado_uni'] ?? ''))->lower()->contains($search);
            });
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
}
