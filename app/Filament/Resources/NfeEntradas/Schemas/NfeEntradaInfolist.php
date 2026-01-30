<?php

namespace App\Filament\Resources\NfeEntradas\Schemas;

use App\Filament\Infolists\Components\DifalEntry;
use App\Filament\Infolists\Components\ProductTableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class NfeEntradaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Detalhes da Nota')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Dados da Nota')
                            ->id('dados-gerais')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('emitente_razao_social')
                                            ->label('Emitente')
                                            ->columnSpan(2)
                                            ->weight('bold'),

                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('nNF')
                                                    ->label('NF-e Nº')
                                                    ->weight('bold'),
                                                TextEntry::make('serie')
                                                    ->label('Série')
                                                    ->weight('bold'),
                                                TextEntry::make('vNfe')
                                                    ->label('Valor Total')
                                                    ->money('BRL')
                                                    ->weight('bold'),
                                            ]),
                                    ]),

                                Section::make('Etiquetas')
                                    ->hidden(fn ($record) => empty($record->tagNamesWithCodeAndValue()))
                                    ->schema([
                                        TextEntry::make('tags')
                                            ->hiddenLabel()
                                            ->live()
                                            ->state(fn ($record) => collect($record->tagNamesWithCodeAndValue())->map(fn ($tag) => "<li>{$tag}</li>")->implode(''))
                                            ->columnSpanFull()
                                            ->html(),
                                    ]),

                                Section::make('Detalhes da Nota')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('nNF')
                                                    ->label('N°'),

                                                TextEntry::make('emitente_razao_social')
                                                    ->label('Razão Social Emitente'),

                                                TextEntry::make('chave')
                                                    ->label('Chave de Acesso')
                                                    ->copyable(),

                                                TextEntry::make('emitente_cnpj')
                                                    ->label('CNPJ Emitente')
                                                    ->formatStateUsing(fn (string $state): string => formatar_cnpj_cpf($state)),

                                                TextEntry::make('vNfe')
                                                    ->label('Valor')
                                                    ->money('BRL'),

                                                TextEntry::make('endereco_emitente_completo')
                                                    ->label('Endereço'),

                                                TextEntry::make('data_emissao')
                                                    ->label('Data Emissão')
                                                    ->dateTime('d/m/Y H:i'),

                                                TextEntry::make('nat_op')
                                                    ->label('Nat. Operação'),
                                            ]),
                                    ]),

                                Section::make('Destinatário')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextEntry::make('destinatario_razao_social')
                                                    ->label('Razão Social'),
                                                TextEntry::make('destinatario_cnpj')
                                                    ->label('CNPJ')
                                                    ->formatStateUsing(fn (string $state): string => formatar_cnpj_cpf($state)),
                                                TextEntry::make('destinatario_fone')
                                                    ->label('Telefone contato'),
                                                TextEntry::make('endereco_destinatario_completo')
                                                    ->label('Endereço'),

                                            ])->columns(2),

                                        Section::make('Impostos')
                                            ->schema([
                                                Group::make()
                                                    ->schema([
                                                        TextEntry::make('vBC')
                                                            ->label('Base Cálc. ICMS')
                                                            ->money('BRL'),
                                                        TextEntry::make('vICMS')
                                                            ->label('Valor ICMS')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('Base Calc. ST')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('Valor ICMS ST')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. Imp. Import.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. FCP UF Dest.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. ICMS UF Remet.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vProd')
                                                            ->label('Total Produtos')
                                                            ->money('BRL'),

                                                    ])
                                                    ->columns(8),

                                                Group::make()
                                                    ->schema([
                                                        TextEntry::make('vFrete')
                                                            ->label('Valor Frete')
                                                            ->money('BRL'),
                                                        TextEntry::make('vSeg')
                                                            ->label('Valor Seguro')
                                                            ->money('BRL'),
                                                        TextEntry::make('vDesc')
                                                            ->label('Desconto')
                                                            ->money('BRL'),
                                                        TextEntry::make('vOutro')
                                                            ->label('Outras Despesas')
                                                            ->money('BRL'),

                                                        TextEntry::make('vIPI')
                                                            ->label('Valor IPI')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. ICMS UF Dest.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vCOFINS')
                                                            ->label('Valor COFINS')
                                                            ->money('BRL'),
                                                        TextEntry::make('vNfe')
                                                            ->label('Valor Total Nota')
                                                            ->money('BRL')
                                                            ->weight('bold'),
                                                    ])
                                                    ->columns(8),
                                            ]),

                                        Section::make('Produtos')
                                            ->schema([
                                                ProductTableEntry::make('product')
                                                    ->hiddenLabel(),

                                            ]),

                                    ]),

                            ]),
                        Tabs\Tab::make('Impostos Debitados')
                            ->id('impostos-debitados')
                            ->schema([
                                Section::make('Diferencial de Alíquota (DIFAL)')
                                    ->description('Cálculo do diferencial de alíquota entre estado de origem e destino. As alíquotas são obtidas automaticamente da tabela de ICMS interestadual do sistema.')
                                    ->schema([
                                        TextEntry::make('vICMSUFDest')
                                            ->label('Total DIFAL')
                                            ->money('BRL')
                                            ->visible(fn ($record) => $record->vICMSUFDest > 0)
                                            ->weight('bold'),
                                        DifalEntry::make('difals')
                                            ->hiddenLabel(),

                                    ]),
                            ]),
                    ]),
            ]);
    }
}
