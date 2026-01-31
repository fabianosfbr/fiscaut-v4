<?php

namespace App\Filament\Resources\CteTomadas\Tables;

use App\Filament\Actions\DownloadPdfCteAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Models\ConhecimentoTransporteEletronico;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class CteTomadasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->defaultSort('data_emissao', 'desc')
            ->columns([
                TextColumn::make('nCTe')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('emitente_razao_social')
                    ->label('Emitente')
                    ->limit(30)
                    ->searchable(['emitente_nome', 'emitente_cnpj'])
                    ->size('sm')
                    ->description(function (ConhecimentoTransporteEletronico $record) {
                        return $record->emitente_cnpj;
                    }),

                TextColumn::make('cfop')
                    ->label('CFOP')
                    ->toggleable()
                    ->alignCenter(),

                ViewColumn::make('nfe_chave')
                    ->view('filament.tables.columns.view-cte-chave-nfe')
                    ->alignCenter()
                    ->label('Chave NFe'),

                TextColumn::make('vCTe')
                    ->label('Valor Total')
                    ->money('BRL'),

                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->toggleable(),

                TextColumn::make('status_cte')
                    ->label('Status')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('tpCTe')
                    ->label('Tipo')
                    ->alignCenter()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Normal',
                        '1' => 'Compl. de valor',
                        '2' => 'Anulação',
                        '3' => 'Substituição',
                    })
                    ->badge(),

                ViewChaveColumn::make('chave')
                    ->label('Chave Acesso')
                    ->tooltip('Chave Acesso do CT-e')
                    ->searchable()
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detalhes'),
                    DownloadXmlAction::make(),
                    DownloadPdfCteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
