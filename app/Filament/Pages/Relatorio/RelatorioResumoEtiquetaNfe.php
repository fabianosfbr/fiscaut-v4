<?php

namespace App\Filament\Pages\Relatorio;

use UnitEnum;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use App\Models\NfeTagAgregadorView;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use App\Services\Relatorios\ListagemProdutosService;

class RelatorioResumoEtiquetaNfe extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Relatório Resumo Etiqueta NFe';

    protected static ?string $title = 'Relatório Resumo Etiqueta NFe';

    protected static ?string $slug = 'relatorio-resumo-etiqueta-nfe';

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';


    protected string $view = 'filament.pages.relatorio.relatorio-resumo-etiqueta-nfe';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->query(function () {
                $issuer = Auth::user()->currentIssuer;
                return NfeTagAgregadorView::query()
                    ->where('issuer_id', $issuer->id)
                    ->whereNotNull('data_entrada')
                    ->orderBy('code', 'ASC')
                    ->orderBy('data_entrada', 'desc');
            })
            ->groups([
                Group::make('code')
                    ->label('Etiqueta')
                    ->collapsible(),
            ])
            ->pluralModelLabel('registros')
            ->defaultGroup('code')
            ->groupingSettingsHidden()
            ->columns([
                TextColumn::make('code')
                    ->label('Código'),
                TextColumn::make('tag')
                    ->label('Etiqueta'),
                TextColumn::make('data_entrada')
                    ->label('Data entrada')
                    ->toggleable()
                    ->date('d/m/Y'),
                TextColumn::make('nNF')
                    ->label(new HtmlString('Nº<br/>NFe')),
                TextColumn::make('vNfe')
                    ->label(new HtmlString('Valor<br/>NFe'))
                    ->money('BRL')
                    ->summarize([
                        Sum::make()->label('Total NFe')->money('BRL')
                    ]),
                TextColumn::make('vBC')
                    ->label(new HtmlString('Base<br/>Cálculo'))
                    ->money('BRL'),
                TextColumn::make('vICMS')
                    ->label(new HtmlString('Valor<br/>ICMS'))
                    ->money('BRL'),
                TextColumn::make('vST')
                    ->label(new HtmlString('Valor<br/>Substituição'))
                    ->money('BRL'),
                TextColumn::make('vIPI')
                    ->label(new HtmlString('Valor<br/>IPI'))
                    ->money('BRL'),
                TextColumn::make('vCOFINS')
                    ->label(new HtmlString('Valor<br/>COFINS'))
                    ->money('BRL'),
            ])
            ->filters([
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

                Filter::make('etiqueta')
                    ->schema([
                        TextInput::make('etiqueta')
                            ->label('Etiqueta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['etiqueta'],
                                function ($q) use ($data) {
                                    return $q->where('code', $data['etiqueta'])
                                        ->orWhere('tag', 'like', '%' . $data['etiqueta'] . '%');
                                },
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['etiqueta']) {
                            return null;
                        }

                        return 'Etiqueta: ' . $data['etiqueta'];
                    })->columnSpan(1),
                Filter::make('numero')
                    ->schema([
                        TextInput::make('numero')
                            ->label('Nº NFSe'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['numero'],
                                function ($q) use ($data) {
                                    return $q->where('numero', $data['numero']);
                                },
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['numero']) {
                            return null;
                        }

                        return 'Nº NFSe: ' . $data['numero'];
                    })->columnSpan(1),
            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                // ...
            ])
            ->toolbarActions([
                // ...
            ]);
    }

    public function getTableRecordKey($record): string
    {
        return uniqid();
    }
}
