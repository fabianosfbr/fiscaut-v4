<?php

namespace App\Filament\Condominio\Pages;

use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class Cobranca extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.condominio.pages.cobranca';

    protected static ?string $title = 'Cobrança';

    public function table(Table $table)
    {
        return $table
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
                        return collect($record['recebimento'] ?? [])->sum(fn ($recb) => (float) data_get($recb, 'vl_emitido_recb', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('juros')
                    ->label('Juros')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn ($recb) => (float) data_get($recb, 'encargos.0.detalhes.juros', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('multa')
                    ->label('Multa')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn ($recb) => (float) data_get($recb, 'encargos.0.detalhes.multa', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('atualiz')
                    ->label('Atualiz.')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn ($recb) => (float) data_get($recb, 'encargos.0.detalhes.atualizacaomonetaria', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('honorarios')
                    ->label('Honorários')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn ($recb) => (float) data_get($recb, 'encargos.0.detalhes.honorarios', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('total')
                    ->label('Total')
                    ->state(function (array $record): float {
                        return collect($record['recebimento'] ?? [])->sum(fn ($recb) => (float) data_get($recb, 'encargos.0.valorcorrigido', 0));
                    })
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.'),
            ]);
    }

    protected function apiData(?string $search, int $page, int|string $recordsPerPage): LengthAwarePaginator
    {
        $issuer = currentIssuer();
        $service = new \App\Services\SuperlogicaConnectionService($issuer);

        $inadimplencias = $service
            ->receita()
            ->listarInadimplencia([
                'idCondominio' => 238,
                'posicaoEm' => now()->format('m/d/Y'),
                'comValoresAtualizadosPorComposicao' => 1,
                'apenasResumoInad' => 0,
                'comDadosDaReceita' => 1,
                'semAcordo' => 1,
                'semProcesso' => 1,
            ]);
        ds($inadimplencias[0]);

        $records = collect($inadimplencias)->forPage($page, $recordsPerPage);

        if (filled($search)) {
            $search = (string) Str::of($search)->trim()->lower();
            $records = $records->filter(function (array $record) use ($search): bool {
                return Str::of((string) ($record['st_unidade_uni'] ?? ''))->lower()->contains($search)
                    || Str::of((string) ($record['st_bloco_uni'] ?? ''))->lower()->contains($search)
                    || Str::of((string) ($record['st_sacado_uni'] ?? ''))->lower()->contains($search);
            });
        }

        return new LengthAwarePaginator(
            $records,
            total: count($inadimplencias),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }
}
