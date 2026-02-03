<?php

namespace App\Filament\Pages\Relatorio;

use App\Models\NotaFiscalEletronica;
use App\Services\Relatorios\ListagemProdutosService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use UnitEnum;

class ListagemProduto extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Listagem de Produtos';

    protected static ?string $title = 'Listagem de Produtos';

    protected static ?string $slug = 'listagem-produtos';

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';

    protected string $view = 'filament.pages.relatorio.listagem-produto';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->records(function (
                ?string $search,
                ?string $sortColumn,
                ?string $sortDirection,
                int $page,
                int|string $recordsPerPage,
            ): LengthAwarePaginator {
                $issuer = Auth::user()->currentIssuer;

                $accumulator = [];

                NotaFiscalEletronica::query()
                    ->where('status_nota', 100)
                    ->where('emitente_cnpj', $issuer->cnpj)
                    ->select(['id', 'xml'])
                    ->orderBy('id')
                    ->chunkById(200, function ($nfes) use (&$accumulator): void {
                        foreach ($nfes as $nfe) {
                            $items = $nfe->produtos ?? [];
                            if (! is_array($items) || $items === []) {
                                continue;
                            }

                            ListagemProdutosService::accumulate($accumulator, $items);
                        }
                    });

                $records = collect($accumulator);

                if (filled($search)) {
                    $search = (string) Str::of($search)->trim()->lower();

                    $records = $records->filter(function (array $record) use ($search): bool {
                        return Str::of((string) ($record['xProd'] ?? ''))->lower()->contains($search)
                            || Str::of((string) ($record['cProd'] ?? ''))->lower()->contains($search)
                            || Str::of((string) ($record['NCM'] ?? ''))->lower()->contains($search)
                            || Str::of((string) ($record['CFOP'] ?? ''))->lower()->contains($search)
                            || Str::of((string) ($record['cEAN'] ?? ''))->lower()->contains($search);
                    });
                }

                $records = $this->sortRecords($records, $sortColumn, $sortDirection);

                $total = $records->count();

                if ($recordsPerPage === 'all') {
                    $recordsPerPage = $total > 0 ? $total : 1;
                    $page = 1;
                }

                $recordsPerPage = max(1, (int) $recordsPerPage);
                $page = max(1, $page);

                $pageRecords = $records->forPage($page, $recordsPerPage);

                return new LengthAwarePaginator(
                    items: $pageRecords->all(),
                    total: $total,
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->columns([
                TextColumn::make('xProd')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('cProd')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('NCM')
                    ->label('NCM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('CFOP')
                    ->label('CFOP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('uCom')
                    ->label('Unid.')
                    ->sortable(),
                TextColumn::make('total_qCom')
                    ->label('Qtd. Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 4, ',', '.')),
                TextColumn::make('total_vProd')
                    ->label('Valor Total')
                    ->sortable()
                    ->money('BRL'),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    private function sortRecords(Collection $records, ?string $sortColumn, ?string $sortDirection): Collection
    {
        $sortColumn = trim((string) $sortColumn);
        $sortDirection = trim((string) $sortDirection);

        $descending = $sortDirection === 'desc';

        if ($sortColumn === '') {
            return $records->sortBy(
                fn (array $record): float => (float) ($record['total_vProd'] ?? 0.0),
                SORT_REGULAR,
                true,
            );
        }

        $numericColumns = [
            'total_qCom',
            'total_vProd',
            'itens',
        ];

        if (in_array($sortColumn, $numericColumns, true)) {
            return $records->sortBy(
                fn (array $record): float => (float) ($record[$sortColumn] ?? 0.0),
                SORT_REGULAR,
                $descending,
            );
        }

        return $records->sortBy(
            fn (array $record): string => (string) ($record[$sortColumn] ?? ''),
            SORT_NATURAL | SORT_FLAG_CASE,
            $descending,
        );
    }
}
