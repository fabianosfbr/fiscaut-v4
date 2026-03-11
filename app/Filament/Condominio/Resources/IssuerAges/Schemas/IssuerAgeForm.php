<?php

namespace App\Filament\Condominio\Resources\IssuerAges\Schemas;

use App\Enums\IssuerAgeTypeEnum;
use App\Enums\IssuerTypeEnum;
use App\Models\Issuer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;


class IssuerAgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ToggleButtons::make('type')
                    ->label('Tipo de Assembleia')
                    ->inline()
                    ->options(IssuerAgeTypeEnum::class)
                    ->default(IssuerAgeTypeEnum::AGO->value)
                    ->live()
                    ->columnSpanFull(),

                FileUpload::make('document_path')
                    ->label(fn(Get $get) => ($get('type')?->value ?? $get('type')) === IssuerAgeTypeEnum::AGO->value ? 'Documento da AGO' : 'Documento da AGE')
                    ->required()
                    ->directory(function ($get) {
                        $issuer = currentIssuer();
                        if (!$issuer) {
                            return null;
                        }

                        return 'rag/' . $issuer->tenant_id . '/' . sanitize($issuer->cnpj) . '/documents';
                    })
                    ->acceptedFileTypes([
                        'application/pdf'
                    ])
                    ->storeFileNamesIn('original_name')
                    ->maxSize(10240) // 10MB in KB
                    ->preserveFilenames()
                    ->columnSpanFull(),

                // AGE Fields Group   
                Fieldset::make('Dados da Assembleia Geral Extraordinária')
                    ->visible(fn(Get $get) => ($get('type')?->value ?? $get('type')) !== IssuerAgeTypeEnum::AGO->value)
                    ->schema([
                        DatePicker::make('vigencia_date')
                            ->label('Data de Vigência')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                        DatePicker::make('data_limite_edital')
                            ->label('Data Limite para Expedição do Edital')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),
                        TextInput::make('prazo_tecnico')
                            ->label('Prazo Técnico')
                            ->columnSpan(1),


                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                // AGO Fields
                Section::make('Informações da AGO')
                    ->visible(fn(Get $get) => ($get('type')?->value ?? $get('type')) === IssuerAgeTypeEnum::AGO->value)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        DatePicker::make('data_limite_ago')
                            ->label('Data Limite da AGO')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpanFull(),

                        Fieldset::make('Dados do Edital')
                            ->schema([
                                DatePicker::make('data_limite_edital')
                                    ->label('Data Limite para Expedição do Edital')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                TextInput::make('prazo_tecnico_edital')
                                    ->label('Prazo Técnico (Edital)'),
                            ])->columnSpanFull(),

                        Fieldset::make('Dados do Mandato')
                            ->schema([
                                DatePicker::make('mandato_fim')
                                    ->label('Fim do Mandato (Síndico)')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                TextInput::make('prazo_tecnico_mandato')
                                    ->label('Prazo Técnico (Mandato)'),
                            ])->columnSpanFull(),

                        Fieldset::make('Dados do Mandato (Conselho)')
                            ->schema([
                                DatePicker::make('mandato_conselho_fim')
                                    ->label('Fim do Mandato (Conselho)')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                TextInput::make('prazo_tecnico_mandato_conselho')
                                    ->label('Prazo Técnico (Conselho)'),
                            ])->columnSpanFull(),

                        Fieldset::make('Dados do Mandato (Banco)')
                            ->schema([
                                DatePicker::make('mandato_banco_fim')
                                    ->label('Fim do Mandato (Banco)')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                TextInput::make('prazo_tecnico_mandato_banco')
                                    ->label('Prazo Técnico (Banco)'),
                            ])->columnSpanFull(),
                    ])->columns(2)
                    ->columnSpanFull(),


                Section::make('Configuração de Boleto')
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn(Get $get) => ($get('type')?->value ?? $get('type')) === IssuerAgeTypeEnum::AGO->value)
                    ->schema([
                        TextInput::make('boleto_dia_vencimento')
                            ->label('Dia do Vencimento')
                            ->numeric()
                            ->maxValue(31)
                            ->minValue(1),
                        Select::make('boleto_tipo_prazo')
                            ->label('Tipo de Prazo')
                            ->options([
                                'uteis' => 'Dias Úteis',
                                'corridos' => 'Dias Corridos',
                            ]),
                        Select::make('boleto_gerado_por')
                            ->label('Gerado Por')
                            ->options([
                                'administradora' => 'Administradora',
                                'garantidora' => 'Garantidora',
                            ]),
                        Select::make('boleto_forma_rateio')
                            ->label('Forma de Rateio')
                            ->options([
                                'ideal' => 'Rateio Ideal',
                                'unidade' => 'Unidade',
                                'm2' => 'Por m²',
                            ]),
                    ])
                    ->columnSpanFull()
                    ->columns(2),

                Section::make('Isenção ou Remuneração')
                    ->visible(fn(Get $get) => ($get('type')?->value ?? $get('type')) === IssuerAgeTypeEnum::AGO->value)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        ToggleButtons::make('tem_isencao_remuneracao')
                            ->label('Há isenção ou remuneração?')
                            ->default(false)
                            ->boolean(trueLabel: 'Isenção', falseLabel: 'Remuneração')
                            ->inline()
                            ->live(),
                        Select::make('quem_recebe_isencao')
                            ->label('Quem recebe?')
                            ->multiple()
                            ->visible(fn(Get $get) => $get('tem_isencao_remuneracao'))
                            ->options(function () {
                                $issuer = currentIssuer();
                                if ($issuer && $issuer->issuer_type === IssuerTypeEnum::ASSOCIACAO) {
                                    return [
                                        'presidente' => 'Presidente',
                                        'vice-presidente' => 'Vice-Presidente',
                                        'conselheiro' => 'Conselheiro',
                                        'administrador' => 'Administrador',
                                    ];
                                }
                                return [
                                    'síndico' => 'Síndico',
                                    'sub-síndico' => 'Sub-Síndico',
                                    'conselheiro' => 'Conselheiro',
                                    'administrador' => 'Administrador',
                                ];
                            }),
                        TextInput::make('valor_isencao_remuneracao')
                            ->label('Valor')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                            ->numeric()
                            ->prefix('R$')
                            ->visible(fn(Get $get) => $get('tem_isencao_remuneracao')),
                    ])
                    ->columnSpanFull()
                    ->columns(2),

                Textarea::make('observacoes')
                    ->label('Observações Gerais')
                    ->rows(3)
                    ->columnSpanFull(),

                Grid::make(3)

                    ->schema([


                    ])->columnSpanFull(),

            ])->columns(3);
    }
}
