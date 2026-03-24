<?php

namespace App\Filament\Condominio\Resources\IssuerControlRecorrencies\Schemas;

use App\Enums\IssuerControlFrequencyEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IssuerControlRecorrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->description('Dados principais do template de recorrência')
                    ->schema([
                        Select::make('type_control_id')
                            ->label('Tipo de Controle')
                            ->relationship('typeControl', 'nome')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        TextInput::make('titulo_template')
                            ->label('Título Template')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('Ex: Limpeza mensal dos filtros - {data}')
                            ->helperText('Use {data} para incluir a data programada no título'),

                        Textarea::make('descricao_template')
                            ->label('Descrição Template')
                            ->placeholder('Descrição padrão para as manutenções geradas...')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Configuração de Frequência')
                    ->description('Defina quando e como as manutenções serão geradas')
                    ->schema([
                        Select::make('frequencia')
                            ->label('Frequência')
                            ->options(IssuerControlFrequencyEnum::class)
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('intervalo', 1)),

                        TextInput::make('intervalo')
                            ->label('Intervalo')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(365)
                            ->helperText('Intervalo entre execuções (ex: a cada 2 semanas = 2)'),

                        TextInput::make('dia_semana')
                            ->label('Dia da Semana')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(6)
                            ->placeholder('0=Dom, 1=Seg, ..., 6=Sáb')
                            ->helperText('Apenas para frequência semanal (0=Domingo, 6=Sábado)'),

                        TextInput::make('dia_mes')
                            ->label('Dia do Mês')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->placeholder('1 a 31')
                            ->helperText('Dia específico do mês (para frequência mensal/anual)'),

                        TextInput::make('mes')
                            ->label('Mês')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->placeholder('1 a 12')
                            ->helperText('Mês específico (apenas para frequência anual)'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Período de Vigência')
                    ->description('Defina o período em que a recorrência estará ativa')
                    ->schema([
                        DatePicker::make('data_inicio')
                            ->label('Data de Início')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Primeira data para geração da recorrência'),

                        DatePicker::make('data_fim')
                            ->label('Data de Fim')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('data_inicio')
                            ->helperText('Deixe vazio para recorrência indefinida'),

                        Toggle::make('ativo')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Recorrências inativas não geram novas recorrências'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Configurações de Geração')
                    ->description('Configurações avançadas para geração automática')
                    ->schema([
                        TextInput::make('gerar_dias_antecedencia')
                            ->label('Gerar com Antecedência')
                            ->required()
                            ->numeric()
                            ->default(30)
                            ->minValue(1)
                            ->maxValue(365)
                            ->suffix('dias')
                            ->helperText('Quantos dias antes gerar as próximas recorrências'),

                        DatePicker::make('ultima_geracao')
                            ->label('Última Geração')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->helperText('Preenchido automaticamente pelo sistema'),

                        DatePicker::make('proxima_geracao')
                            ->label('Próxima Geração')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->helperText('Calculado automaticamente pelo sistema'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
