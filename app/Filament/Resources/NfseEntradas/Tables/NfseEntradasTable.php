<?php

namespace App\Filament\Resources\NfseEntradas\Tables;

use Filament\Tables\Table;
use App\Models\GeneralSetting;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Actions\SugerirEtiquetaAction;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Actions\ClassificarDocumentoAction;
use App\Filament\Actions\RemoverClassificaoNfeAction;
use App\Filament\Actions\ToggleEscrituacaoEmLoteAction;
use App\Filament\Actions\ClassificarDocumentoEmLoteAction;


class NfseEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->distinct()
                    ->where('tomador_cnpj', Auth::user()->currentIssuer->cnpj);
            })
            ->defaultSort('data_emissao', 'desc')
            ->columns([
                TextColumn::make('numero')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prestador_servico')
                    ->label('Empresa')
                    ->limit(30)
                    ->searchable()
                    ->size('sm')
                    ->description(function (Model $record) {
                        return $record->prestador_cnpj;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),

                IconColumn::make('apurada.status')
                    ->label('Apurada')
                    ->boolean()
                    ->default(false)
                    ->alignment(Alignment::Center)
                    ->toggleable(),



                TextColumn::make('valor_servico')
                    ->label('Valor')
                    ->money('BRL'),


                TextColumn::make('data_entrada')
                    ->label('Entrada')
                    ->sortable()
                    ->toggleable()
                    ->date('d/m/Y'),

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

                TextColumn::make('cancelada')
                    ->label('Status')
                    ->toggleable()
                    ->formatStateUsing(function ($record) {
                        return $record->cancelada ? 'Cancelada' : 'Ativa';
                    })
                    ->badge(),


                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->sortable()
                    ->toggleable()
                    ->date('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detalhes'),
                    ToggleEscrituracaoAction::make(),
                    ClassificarDocumentoAction::make(),
                    RemoverClassificaoNfeAction::make(),

                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ToggleEscrituacaoEmLoteAction::make(),

                    ClassificarDocumentoEmLoteAction::make()
                        ->after(function () {

                            Notification::make()
                                ->title('Etiquetas aplicadas com sucesso')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
