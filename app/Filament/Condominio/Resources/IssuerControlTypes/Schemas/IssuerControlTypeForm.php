<?php

namespace App\Filament\Condominio\Resources\IssuerControlTypes\Schemas;

use App\Enums\IssuerControlPriorityEnum;
use App\Enums\IssuerControlTypeEnum;
use App\Enums\ManutencaoCategoriaEnum;
use App\Enums\ManutencaoPrioridadeEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IssuerControlTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->description('Dados principais do tipo de controle')
                    ->schema([
                        TextInput::make('nome')
                            ->label('Nome do Tipo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Limpeza de Filtros'),

                        Select::make('categoria')
                            ->label('Categoria')
                            ->options(IssuerControlTypeEnum::class)
                            ->default('preventiva')
                            ->required()
                            ->native(false),

                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->placeholder('Descreva detalhadamente este tipo de controle...')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Configurações Padrão')
                    ->description('Configurações padrão deste tipo')
                    ->schema([
                        TextInput::make('alerta_dias_antecedencia')
                            ->label('Alerta Padrão (dias)')
                            ->required()
                            ->numeric()
                            ->default(7)
                            ->maxValue(365)
                            ->suffix('dias')
                            ->helperText('Quantos dias antes alertar sobre o controle'),

                        Select::make('prioridade')
                            ->label('Prioridade Padrão')
                            ->options(IssuerControlPriorityEnum::class)
                            ->default('media')
                            ->required()
                            ->native(false),

                        TextInput::make('responsavel_padrao')
                            ->label('Responsável Padrão')
                            ->placeholder('Nome do responsável padrão')
                            ->maxLength(255),

                        Toggle::make('ativo')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Tipos inativos não aparecem na programação'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
