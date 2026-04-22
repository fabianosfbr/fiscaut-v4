<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Schemas;

use App\Enums\AssembleiaStatusEnum;
use App\Enums\AtaStatusEnum;
use App\Enums\DeliberacaoStatusEnum;
use App\Enums\IssuerAgeTypeEnum;
use App\Enums\IssuerTypeEnum;
use App\Filament\Infolists\Components\IssuerControlLogEntry;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class IssuerAssembleiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Resumo')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        ToggleButtons::make('type')
                                            ->label('Tipo de Assembleia')
                                            ->inline()
                                            ->options(IssuerAgeTypeEnum::class)
                                            ->default(IssuerAgeTypeEnum::AGO->value)
                                            ->live()
                                            ->columnSpanFull(),
                                        Fieldset::make('Dados da Assembleia Geral Extraordinária')
                                            ->visible(fn (Get $get) => ($get('type')?->value ?? $get('type')) !== IssuerAgeTypeEnum::AGO->value)
                                            ->schema([
                                                DatePicker::make('vigencia_date')
                                                    ->label('Data de Vigência')
                                                    ->native(true)
                                                    ->displayFormat('d/m/Y')
                                                    ->columnSpan(1),
                                                Select::make('assembleia_status')
                                                    ->label('Status da Assembleia')
                                                    ->options(AssembleiaStatusEnum::toArray())
                                                    ->default(AssembleiaStatusEnum::DRAFT->value)
                                                    ->columnSpan(1),
                                                Select::make('ata_status')
                                                    ->label('Status da ATA')
                                                    ->options(AtaStatusEnum::toArray())
                                                    ->default(AtaStatusEnum::NOT_STARTED->value)
                                                    ->columnSpan(1),
                                                Select::make('deliberacao_status')
                                                    ->label('Status da Deliberação')
                                                    ->options(DeliberacaoStatusEnum::toArray())
                                                    ->default(DeliberacaoStatusEnum::PENDING->value)
                                                    ->columnSpan(1),
                                                DatePicker::make('data_realizacao')
                                                    ->label('Data de Realização')
                                                    ->native(true)
                                                    ->displayFormat('d/m/Y')
                                                    ->columnSpan(1),
                                                DatePicker::make('data_limite_edital')
                                                    ->label('Data Limite para Expedição do Edital')
                                                    ->native(true)
                                                    ->displayFormat('d/m/Y')
                                                    ->columnSpan(1),
                                                TextInput::make('prazo_tecnico')
                                                    ->label('Prazo Técnico')
                                                    ->columnSpan(1),

                                            ])
                                            ->columns(3)
                                            ->columnSpanFull(),
                                        AdvancedFileUpload::make('document_path')
                                            ->label('Anexar Edital')
                                            ->pdfPreviewHeight(400) // Customize preview height
                                            ->pdfDisplayPage(1) // Set default page
                                            ->pdfToolbar(true) // Enable toolbar
                                            ->pdfZoomLevel(100) // Set zoom level
                                            ->pdfNavPanes(true) // Enable navigation panes
                                            ->required(fn (Get $get) => ($get('assembleia_status') ?? $get('assembleia_status')?->value) !== AssembleiaStatusEnum::DRAFT->value)
                                            ->disk('local')
                                            ->directory(function ($get) {
                                                $issuer = currentIssuer();
                                                if (! $issuer) {
                                                    return null;
                                                }

                                                return 'rag/'.$issuer->tenant_id.'/'.sanitize($issuer->cnpj).'/documents';
                                            })
                                            ->visibility('private')
                                            ->acceptedFileTypes([
                                                'application/pdf',
                                            ])
                                            ->maxSize(10240) // 10MB in KB
                                            ->storeFileNamesIn('original_name')
                                            ->preserveFilenames()
                                            ->helperText('Formatos permitidos: PDF. Tamanho máximo: 10MB')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Informações da AGO')
                                    ->visible(fn (Get $get) => ($get('type')?->value ?? $get('type')) === IssuerAgeTypeEnum::AGO->value)
                                    ->collapsible()
                                    ->schema([
                                        DatePicker::make('data_limite_ago')
                                            ->label('Data Limite da AGO')
                                            ->native(true)
                                            ->displayFormat('d/m/Y')
                                            ->columnSpan(1),
                                        Select::make('assembleia_status')
                                            ->label('Status da Assembleia')
                                            ->options(AssembleiaStatusEnum::toArray())
                                            ->default(AssembleiaStatusEnum::DRAFT->value)
                                            ->columnSpan(1),
                                        Select::make('ata_status')
                                            ->label('Status da ATA')
                                            ->options(AtaStatusEnum::toArray())
                                            ->default(AtaStatusEnum::NOT_STARTED->value)
                                            ->columnSpan(1),
                                        Select::make('deliberacao_status')
                                            ->label('Status da Deliberação')
                                            ->options(DeliberacaoStatusEnum::toArray())
                                            ->default(DeliberacaoStatusEnum::PENDING->value)
                                            ->columnSpan(1),
                                        DatePicker::make('data_realizacao')
                                            ->label('Data de Realização')
                                            ->native(true)
                                            ->displayFormat('d/m/Y')
                                            ->columnSpan(1),

                                        Fieldset::make('Dados do Edital')
                                            ->schema([
                                                DatePicker::make('data_limite_edital')
                                                    ->label('Data Limite para Expedição do Edital')
                                                    ->native(true)
                                                    ->displayFormat('d/m/Y'),
                                                TextInput::make('prazo_tecnico_edital')
                                                    ->label('Prazo Técnico (Edital)'),
                                            ])->columnSpanFull(),

                                        Fieldset::make('Dados do Mandato')
                                            ->schema([
                                                DatePicker::make('mandato_fim')
                                                    ->label('Fim do Mandato (Síndico)')
                                                    ->native(true)
                                                    ->displayFormat('d/m/Y'),
                                                TextInput::make('prazo_tecnico_mandato')
                                                    ->label('Prazo Técnico (Mandato)'),
                                            ])->columnSpanFull(),

                                        Fieldset::make('Dados do Mandato (Conselho)')
                                            ->schema([
                                                DatePicker::make('mandato_conselho_fim')
                                                    ->label('Fim do Mandato (Conselho)')
                                                    ->native(true)
                                                    ->displayFormat('d/m/Y'),
                                                TextInput::make('prazo_tecnico_mandato_conselho')
                                                    ->label('Prazo Técnico (Conselho)'),
                                            ])->columnSpanFull(),

                                        Fieldset::make('Dados do Mandato (Banco)')
                                            ->schema([
                                                DatePicker::make('mandato_banco_fim')
                                                    ->label('Fim do Mandato (Banco)')
                                                    ->native(true)
                                                    ->displayFormat('d/m/Y'),
                                                TextInput::make('prazo_tecnico_mandato_banco')
                                                    ->label('Prazo Técnico (Banco)'),
                                            ])->columnSpanFull(),
                                    ])->columns(6)
                                    ->columnSpanFull(),

                                Section::make('Configuração de Boleto')
                                    ->collapsible()
                                    ->visible(fn (Get $get) => ($get('type')?->value ?? $get('type')) === IssuerAgeTypeEnum::AGO->value)
                                    ->schema([
                                        TextInput::make('boleto_dia_vencimento')
                                            ->label('Dia do Vencimento')
                                            ->numeric()
                                            ->mask('99')
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
                                Section::make('Configuração da Assembleia')
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('num_day_control')
                                            ->label('Número de Dias para Controle')
                                            ->required()
                                            ->default(5)
                                            ->numeric()
                                            ->minValue(1)
                                            ->columnSpan(1),

                                    ])
                                    ->columnSpanFull()
                                    ->columns(4),
                                Section::make('Isenção ou Remuneração')
                                    ->visible(fn (Get $get) => ($get('type')?->value ?? $get('type')) === IssuerAgeTypeEnum::AGO->value)
                                    ->collapsible()
                                    ->schema([
                                        CheckboxList::make('tem_isencao_remuneracao')
                                            ->label('Há isenção ou remuneração?')
                                            ->columns(2)
                                            ->options([
                                                'isencao' => 'Isenção',
                                                'remuneracao' => 'Remuneração',
                                            ])
                                            ->live()
                                            ->columnSpanFull(),
                                        Select::make('quem_recebe_isencao')
                                            ->label('Quem recebe a isenção?')
                                            ->multiple()
                                            ->visible(fn (Get $get) => in_array('isencao', $get('tem_isencao_remuneracao') ?? []))
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
                                        TextInput::make('valor_isencao')
                                            ->label('Valor da Isenção')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                            ->numeric()
                                            ->prefix('R$')
                                            ->visible(fn (Get $get) => in_array('isencao', $get('tem_isencao_remuneracao') ?? [])),
                                        Select::make('quem_recebe_remuneracao')
                                            ->label('Quem recebe a remuneração?')
                                            ->multiple()
                                            ->visible(fn (Get $get) => in_array('remuneracao', $get('tem_isencao_remuneracao') ?? []))
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
                                        TextInput::make('valor_remuneracao')
                                            ->label('Valor da Remuneração')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                            ->numeric()
                                            ->prefix('R$')
                                            ->visible(fn (Get $get) => in_array('remuneracao', $get('tem_isencao_remuneracao') ?? [])),
                                    ])
                                    ->columnSpanFull()
                                    ->columns(2),

                                Textarea::make('observacoes')
                                    ->label('Observações Gerais')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                        Tab::make('Histórico')
                            ->schema([
                                Section::make('Últimas ações')
                                    ->schema([
                                        IssuerControlLogEntry::make('log')
                                            ->hiddenLabel(),
                                    ])
                                    ->collapsible(),
                            ])->columnSpanFull(),

                    ])->columnSpanFull(),

            ]);
    }
}
