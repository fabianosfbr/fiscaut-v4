<?php

namespace App\Filament\Resources\NfeEntradas\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\GeneralSetting;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use App\Models\NotaFiscalEletronica;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Actions\ManifestarNfeAction;
use App\Filament\Actions\DownloadPdfNfeAction;
use App\Services\Tagging\TagSuggestionService;
use App\Filament\Actions\SugerirEtiquetaAction;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Actions\ClassificarDocumentoAction;
use App\Filament\Actions\RemoverClassificaoNfeAction;

class NfeEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data_emissao', 'desc')
            ->paginated([10, 25, 50, 100])
            ->recordUrl(null)
            ->columns([
                TextColumn::make('nNF')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('emitente_razao_social')
                    ->label('Empresa')
                    ->limit(30)
                    ->searchable(['emitente_razao_social', 'emitente_cnpj'])
                    ->size('sm')
                    ->description(function (NotaFiscalEletronica $record) {

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
                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->toggleable(),

                TextColumn::make('data_entrada')
                    ->label('Entrada')
                    ->toggleable()
                    ->date('d/m/Y'),

                IconColumn::make('processed')
                    ->label('Apurada')
                    ->boolean()
                    ->alignment(Alignment::Center)
                    ->toggleable(),

                TextColumn::make('vNfe')
                    ->label('Valor Total')
                    ->sortable()
                    ->money('BRL'),

                TagBadgesColumn::make('tagged')
                    ->label('Etiqueta')
                    ->alignCenter()
                    ->emptyText('')
                    ->showTagCode(function () {
                        $issuerId = Auth::user()->currentIssuer->id;
                        return GeneralSetting::getValue(
                            name: 'configuracoes_gerais',
                            key: 'isNfeMostrarCodigoEtiqueta',
                            default: false,
                            issuerId: $issuerId
                        );
                    })
                    ->toggleable(),


                TextColumn::make('status_nota')
                    ->label('Status')
                    ->toggleable()
                    ->badge(),

                TextColumn::make('status_manifestacao')
                    ->label('Manifestação')
                    ->badge(),

                ViewChaveColumn::make('chave')
                    ->label('Chave')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    SugerirEtiquetaAction::make(),
                    ViewAction::make(),
                    ManifestarNfeAction::make(),
                    ToggleEscrituracaoAction::make(),
                    ClassificarDocumentoAction::make(),
                    RemoverClassificaoNfeAction::make(),
                    DownloadXmlAction::make(),
                    DownloadPdfNfeAction::make(),

                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
