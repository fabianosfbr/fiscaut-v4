<?php

namespace App\Filament\Condominio\Resources\IssuerAges\Schemas;

use App\Enums\IssuerAgeTypeEnum;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IssuerAgeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Gerais')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Tipo de Assembleia')
                                    ->badge()
                                    ->color(fn(IssuerAgeTypeEnum $state): string => match ($state) {
                                        IssuerAgeTypeEnum::AGO => 'success',
                                        IssuerAgeTypeEnum::AGE => 'warning',
                                    }),

                            ]),
                    ])->columnSpanFull(),

                Section::make('Cronograma e Prazos')
                    ->description('Datas importantes e prazos técnicos relacionados à assembleia.')

                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Group::make([
                                    TextEntry::make('vigencia_date')
                                        ->label('Data de Vigência')
                                        ->date('d/m/Y')
                                        ->visible(fn($record) => $record->type === IssuerAgeTypeEnum::AGE)
                                        ->icon('heroicon-m-calendar'),

                                    TextEntry::make('data_limite_edital')
                                        ->label('Limite do Edital')
                                        ->date('d/m/Y'),

                                    TextEntry::make('prazo_tecnico')
                                        ->label('Prazo Técnico (Edital)')
                                        ->placeholder('Não definido')
                                        ->visible(fn($record) => $record->type === IssuerAgeTypeEnum::AGE),

                                    TextEntry::make('prazo_tecnico_edital')
                                        ->label('Prazo Técnico (Edital)')
                                        ->placeholder('Não definido'),

                                    TextEntry::make('data_limite_ago')
                                        ->label('Limite AGO')
                                        ->date('d/m/Y')
                                        ->placeholder('Não definido')
                                        ->color('danger'),
                                ])->columnSpan(1),

                                Group::make([
                                    TextEntry::make('mandato_fim')
                                        ->label('Fim Mandato Síndico')
                                        ->placeholder('Não definido')
                                        ->date('d/m/Y'),

                                    TextEntry::make('prazo_tecnico_mandato')
                                        ->label('Prazo Técnico (Mandato)')
                                        ->placeholder('Não definido'),

                                    TextEntry::make('mandato_conselho_fim')
                                        ->label('Fim Mandato Conselho')
                                        ->date('d/m/Y'),

                                    TextEntry::make('prazo_tecnico_mandato_conselho')
                                        ->label('Prazo Técnico (Conselho)')
                                        ->placeholder('Não definido'),
                                ])->columnSpan(1),

                                Group::make([
                                    TextEntry::make('mandato_banco_fim')
                                        ->label('Fim Mandato Banco')
                                        ->placeholder('Não definido')
                                        ->date('d/m/Y'),

                                    TextEntry::make('prazo_tecnico_mandato_banco')
                                        ->label('Prazo Técnico (Banco)')
                                        ->placeholder('Não definido'),
                                ])->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Financeiro e Isenções')
                    ->visible(fn($record) => $record->type === IssuerAgeTypeEnum::AGO)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    TextEntry::make('boleto_dia_vencimento')
                                        ->label('Dia de Vencimento')
                                        ->suffix(' de cada mês'),

                                    TextEntry::make('boleto_tipo_prazo')
                                        ->label('Tipo de Prazo')
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            'uteis' => 'Dias Úteis',
                                            'corridos' => 'Dias Corridos',
                                        }),

                                    TextEntry::make('boleto_gerado_por')
                                        ->label('Boleto Gerado Por')
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            'administradora' => 'Administradora',
                                            'garantidora' => 'Garantidora',
                                        }),

                                    TextEntry::make('boleto_forma_rateio')
                                        ->label('Forma de Rateio')
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            'ideal' => 'Rateio Ideal',
                                            'unidade' => 'Unidade',
                                            'm2' => 'Por m²',
                                        }),
                                ])->columnSpan(1),

                                Group::make([
                                    IconEntry::make('tem_isencao_remuneracao')
                                        ->label('Possui Isenção/Remuneração?')
                                        ->boolean(),

                                    TextEntry::make('valor_isencao_remuneracao')
                                        ->label('Valor da Isenção')
                                        ->money('BRL')
                                        ->visible(fn($record) => $record->tem_isencao_remuneracao),

                                    TextEntry::make('quem_recebe_isencao')
                                        ->label('Beneficiários da Isenção')
                                        ->listWithLineBreaks()
                                        ->bulleted()
                                        ->visible(fn($record) => $record->tem_isencao_remuneracao),
                                ])->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Documentação e Observações')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('document_path')
                                    ->label('Documento Anexo')
                                    ->formatStateUsing(fn($state) => 'Visualizar Documento')
                                    ->url(fn($record) => route('issuer-age.document.show', $record), true)
                                    ->icon('heroicon-m-document-arrow-down')
                                    ->color('primary')
                                    ->visible(fn($record) => !empty($record->document_path)),

                                TextEntry::make('observacoes')
                                    ->label('Observações Gerais')
                                    ->markdown()
                                    ->placeholder('Nenhuma observação registrada.'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
