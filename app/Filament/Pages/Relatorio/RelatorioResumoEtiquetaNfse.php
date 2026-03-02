<?php

namespace App\Filament\Pages\Relatorio;

use App\Models\NfseTagAgregadorView;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class RelatorioResumoEtiquetaNfse extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Relatório Resumo Etiqueta NFS-e';

    protected static ?string $title = 'Relatório Resumo Etiqueta NFS-e';

    protected static ?string $slug = 'relatorio-resumo-etiqueta-nfse';

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';

    protected string $view = 'filament.pages.relatorio.relatorio-resumo-etiqueta-nfse';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->query(function () {
                $issuer = currentIssuer();

                return NfseTagAgregadorView::query()
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
                TextColumn::make('numero')
                    ->label(new HtmlString('Nº NFSe')),
                TextColumn::make('valor_servico')
                    ->label(new HtmlString('Valor NFSe'))
                    ->money('BRL')
                    ->summarize([
                        Sum::make()->label('Total NFSe')->money('BRL'),
                    ]),
            ])
            ->filters([
                // ...
            ])
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
