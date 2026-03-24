<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Schemas;

use App\Enums\IssuerControlPriorityEnum;
use App\Enums\IssuerControlStatusEnum;
use App\Enums\IssuerControlTypeEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IssuerControlForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->description('Dados principais do controle')
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Nome do Controle')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('Ex: Limpeza dos filtros do ar condicionado'),

                        Select::make('type_control_id')
                            ->label('Tipo de Controle')
                            ->relationship('typeControl', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->placeholder('Descreva detalhadamente o controle...')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Classificação e Prioridade')
                    ->description('Tipo, status e prioridade do controle')
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(IssuerControlTypeEnum::class)
                            ->default('preventiva')
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->label('Status')
                            ->options(IssuerControlStatusEnum::class)
                            ->default('programada')
                            ->required()
                            ->native(false),

                        Select::make('prioridade')
                            ->label('Prioridade')
                            ->options(IssuerControlPriorityEnum::class)
                            ->default('media')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Programação e Execução')
                    ->description('Datas de programação e execução')
                    ->schema([
                        DatePicker::make('data_programada')
                            ->label('Data Programada')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DateTimePicker::make('data_execucao')
                            ->label('Data de Execução')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Quando a execução do controle foi iniciada'),

                        DateTimePicker::make('data_conclusao')
                            ->label('Data de Conclusão')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Quando a execução do controle foi finalizada'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Responsáveis e Local')
                    ->description('Responsáveis pela execução e localização')
                    ->schema([
                        TextInput::make('usuario_responsavel')
                            ->label('Responsável')
                            ->placeholder('Informe o responsável pela execução do controle'),

                        TextInput::make('local')
                            ->label('Local')
                            ->maxLength(200)
                            ->placeholder('Ex: Sala de máquinas - 2º andar'),

                        TextInput::make('equipamento')
                            ->label('Equipamento')
                            ->maxLength(200)
                            ->placeholder('Ex: Ar condicionado central - Unidade 01'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Custos')
                    ->description('Custos estimados e reais do controle')
                    ->schema([
                        TextInput::make('custo_estimado')
                            ->label('Custo Estimado')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->placeholder('0,00'),

                        TextInput::make('custo_real')
                            ->label('Custo Real')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.01)
                            ->placeholder('0,00')
                            ->helperText('Custo real após execução'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Recorrência e Anexos')
                    ->description('Configurações de recorrência e documentos')
                    ->schema([
                        Select::make('recorrencia_id')
                            ->label('Template de Recorrência')
                            ->relationship('recorrencia', 'titulo_template')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione se faz parte de uma recorrência')
                            ->helperText('Vincule a um template de recorrência se aplicável'),

                        FileUpload::make('anexos')
                            ->label('Anexos')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->helperText('Documentos, fotos, orçamentos, etc.')
                            ->columnSpan(2),

                        Textarea::make('observacoes')
                            ->label('Observações')
                            ->placeholder('Observações adicionais sobre o controle...')
                            ->rows(4)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
