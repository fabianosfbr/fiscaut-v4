<?php

namespace App\Filament\Resources\Layouts\Schemas;

use App\Models\HistoricoContabil;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class LayoutRuleSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('rule_type')
                    ->label('Tipo da Regra')
                    ->columns(3)
                    ->live()
                    ->default('data_da_operacao')
                    ->options([
                        'data_da_operacao' => 'Data da Operação',
                        'operacao_de_debito' => 'Operação de Débito',
                        'operacao_de_credito' => 'Operação de Crédito',
                        'valor_da_operacao' => 'Valor da Operação',
                    ])
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('position')
                    ->label('Posição')
                    ->required()
                    ->numeric(),

                TextInput::make('name')
                    ->label('Nome da Regra')
                    ->required()
                    ->maxLength(255),

                Select::make('data_source_type')
                    ->label('Tipo de Fonte de Dados')
                    ->options([
                        'column' => 'Coluna',
                        'constant' => 'Constante',
                        'query' => 'Consulta',
                        'parametros_gerais' => 'Parâmetros Gerais',
                    ])
                    ->required()
                    ->reactive() // Atualiza os campos dependentes quando o valor muda
                    ->visible(function (Get $get) {
                        return $get('rule_type') !== 'historico_contabil';
                    })
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state !== 'column' && $state !== 'query' && $state !== 'parametros_gerais') {
                            $set('data_source', null);
                        }
                        if ($state !== 'constant') {
                            $set('data_source_constant', null);
                        }
                        if ($state !== 'parametros_gerais') {
                            $set('data_source_parametros_gerais_target_columns', null);
                        }
                    }),

                Select::make('data_source')
                    ->label('Coluna de Layout')
                    ->options(function (Get $get, RelationManager $livewire): array {
                        if ($get('data_source_type') === 'column') {
                            // Obtém as colunas cadastradas no Layout (LayoutColumn)
                            $layoutColumns = $livewire->getOwnerRecord()->layoutColumns;

                            // Formata as opções para o Select
                            return $layoutColumns->pluck('target_column_name', 'excel_column_name')->toArray();
                        }

                        return [];
                    })
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'column')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'column')
                    ->searchable(),

                Select::make('date_adjustment')
                    ->label('Ajuste de Data')
                    ->default('same')
                    ->options([
                        'same' => 'Mesma data',
                        'd-1' => 'D-1 (dia anterior)',
                        'd+1' => 'D+1 (próximo dia)',
                    ])
                    ->required()
                    ->visible(function (Get $get) {
                        return $get('rule_type') === 'data_da_operacao';
                    })
                    ->helperText('Define como a data será ajustada durante a importação'),

                TextInput::make('data_source_constant')
                    ->label('Código do Plano de Contas')
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'constant')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'constant'),

                Select::make('data_source_table')
                    ->label('Tabela')
                    ->options([
                        'contabil_plano_de_contas' => 'Plano de Contas',
                        'contabil_bancos' => 'Bancos',
                        'contabil_clientes' => 'Clientes',
                        'contabil_fornecedores' => 'Fornecedores',
                    ])
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'query')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'query')
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('data_source_attribute', null);
                    }),

                Select::make('data_source_attribute')
                    ->label('Atributo')
                    ->options(function (Get $get): array {
                        $table = $get('data_source_table');
                        if ($table === 'contabil_plano_de_contas') {
                            return [
                                'nome' => 'Nome',
                                'codigo' => 'Conta Contábil',
                                'classificacao' => 'Classificação',
                            ];
                        } elseif ($table === 'contabil_bancos') {
                            return [
                                'nome' => 'Nome',
                                'cnpj' => 'CNPJ',
                                'conta' => 'Nº da Conta',
                            ];
                        } elseif ($table === 'contabil_clientes') {
                            return [
                                'cnpj' => 'CNPJ',
                                'nome' => 'Razão Social',
                            ];
                        } elseif ($table === 'contabil_fornecedores') {
                            return [
                                'cnpj' => 'CNPJ',
                                'nome' => 'Razão Social',
                            ];
                        }

                        return [];
                    })
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'query')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'query')
                    ->reactive(),

                Select::make('data_source_condition')
                    ->label('Condição')
                    ->options([
                        '=' => 'Igual a',
                        'like' => 'Contém',
                    ])
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'query')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'query'),

                Select::make('data_source_value_type')
                    ->label('Tipo de Valor da Pesquisa')
                    ->options([
                        'constant' => 'Valor Constante',
                        'column' => 'Coluna do Excel',
                    ])
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'query')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'query')
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state !== 'column') {
                            $set('data_source_search_value', null);
                        }
                        if ($state !== 'constant') {
                            $set('data_source_search_constant', null);
                        }
                    }),

                Select::make('data_source_search_value')
                    ->label('Coluna do Excel para Pesquisa')
                    ->options(function (Get $get, RelationManager $livewire): array {
                        if ($get('data_source_type') === 'query' && $get('data_source_value_type') === 'column') {
                            // Obtém as colunas cadastradas no Layout (LayoutColumn)
                            $layoutColumns = $livewire->getOwnerRecord()->layoutColumns;

                            // Formata as opções para o Select
                            return $layoutColumns->pluck('target_column_name', 'excel_column_name')->toArray();
                        }

                        return [];
                    })
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'query' && $get('data_source_value_type') === 'column')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'query' && $get('data_source_value_type') === 'column')
                    ->searchable(),

                TextInput::make('data_source_search_constant')
                    ->label('Valor Constante para Pesquisa')
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'query' && $get('data_source_value_type') === 'constant')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'query' && $get('data_source_value_type') === 'constant'),

                Select::make('data_source_historico')
                    ->label('Cód. Histórico')
                    ->required()
                    ->options(function () {

                        $values = HistoricoContabil::where('issuer_id', Auth::user()->currentIssuer->id)
                            ->orderBy('codigo', 'asc')
                            ->get()
                            ->map(function ($item) {
                                $item->codigo_descricao = $item->codigo.' | '.$item->descricao;

                                return $item;
                            })

                            ->pluck('codigo_descricao', 'codigo');

                        return $values;
                    })
                    ->required(fn (Get $get): bool => $get('data_source_type') === 'constant' || $get('data_source_type') === 'query' && $get('data_source_table') === 'contabil_clientes' || $get('data_source_table') === 'contabil_fornecedores')
                    ->visible(fn (Get $get): bool => $get('data_source_type') === 'constant' || $get('data_source_type') === 'query' && $get('data_source_table') === 'contabil_clientes' || $get('data_source_table') === 'contabil_fornecedores'),

                Select::make('condition_type')
                    ->label('Tipo de Condição')
                    ->required()
                    ->options([
                        'none' => 'Nenhuma',
                        'if' => 'Se',
                    ])
                    ->default('none')
                    ->live()
                    ->visible(function (Get $get) {
                        return $get('rule_type') !== 'historico_contabil';
                    })
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state === 'none') {
                            $set('condition_data_source', null);
                            $set('switchCases', []);
                        }
                    }),

                Select::make('condition_data_source_type')
                    ->label('Tipo de Fonte de Dados da Condição')
                    ->options([
                        'column' => 'Coluna',
                        'constant' => 'Constante',
                    ])
                    ->required(fn (Get $get): bool => $get('condition_type') === 'if')
                    ->visible(fn (Get $get): bool => $get('condition_type') === 'if')
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state !== 'column' && $state !== 'query') {
                            $set('condition_data_source', null);
                        }
                        if ($state !== 'constant') {
                            $set('condition_data_source_constant', null);
                        }
                    }),

                Select::make('condition_data_source')
                    ->label('Coluna de Layout (Condição)')
                    ->options(function (Get $get, RelationManager $livewire): array {
                        if ($get('condition_data_source_type') === 'column') {
                            // Obtém as colunas cadastradas no Layout (LayoutColumn)
                            $layoutColumns = $livewire->getOwnerRecord()->layoutColumns;

                            // Formata as opções para o Select
                            return $layoutColumns->pluck('target_column_name', 'excel_column_name')->toArray();
                        }

                        return [];
                    })
                    ->required(fn (Get $get): bool => $get('condition_type') === 'if' && $get('condition_data_source_type') === 'column')
                    ->visible(fn (Get $get): bool => $get('condition_type') === 'if' && $get('condition_data_source_type') === 'column')
                    ->searchable(),

                TextInput::make('condition_data_source_constant')
                    ->label('Valor Constante (Condição)')
                    ->required(fn (Get $get): bool => $get('condition_type') === 'if' && $get('condition_data_source_type') === 'constant')
                    ->visible(fn (Get $get): bool => $get('condition_type') === 'if' && $get('condition_data_source_type') === 'constant'),

                Select::make('condition_operator')
                    ->label('Operador da Condição')
                    ->live()
                    ->options([
                        '=' => 'Igual a',
                        '!=' => 'Diferente de',
                        '>' => 'Maior que',
                        '<' => 'Menor que',
                        '>=' => 'Maior ou igual a',
                        '<=' => 'Menor ou igual a',
                        'contains' => 'Contém',
                        'not_contains' => 'Não Contém',
                        'empty' => 'Vazio',
                        'not_empty' => 'Não Vazio',
                    ])
                    ->required(fn (Get $get): bool => $get('condition_type') === 'if')
                    ->visible(fn (Get $get): bool => $get('condition_type') === 'if'),

                TextInput::make('condition_value')
                    ->label('Valor da Condição')
                    ->required(fn (Get $get): bool => $get('condition_type') === 'if' && ! in_array($get('condition_operator'), ['empty', 'not_empty']))
                    ->visible(fn (Get $get): bool => $get('condition_type') === 'if' && ! in_array($get('condition_operator'), ['empty', 'not_empty'])),

                TextInput::make('default_value')
                    ->label('Valor Padrão')
                    ->visible(function (Get $get) {
                        return $get('rule_type') !== 'historico_contabil';
                    })
                    ->nullable(),

                Select::make('data_source_historical_columns')
                    ->label('Colunas do Excel para Processar Parâmetros')
                    ->multiple()
                    ->options(function (Get $get, RelationManager $livewire): array {
                        $layoutColumns = $livewire->getOwnerRecord()->layoutColumns;

                        // Formata as opções para o Select
                        return $layoutColumns->pluck('target_column_name', 'excel_column_name')->toArray();
                    })
                    ->visible(function (Get $get) {
                        return $get('rule_type') === 'historico_contabil';
                    }),

                Toggle::make('is_sanitize')
                    ->label('Limpar Caracteres Especiais')
                    ->default(false)
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }
}
