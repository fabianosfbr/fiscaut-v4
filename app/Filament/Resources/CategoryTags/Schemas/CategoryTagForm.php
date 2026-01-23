<?php

namespace App\Filament\Resources\CategoryTags\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->description('Dados principais da categoria de etiqueta')
                    ->schema([
                        TextInput::make('order')
                            ->label('Ordem')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('grupo')
                            ->label('Código grupo do produto')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('conta_contabil')
                            ->label('Conta Contábil')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),

                        Select::make('tipo_item')
                            ->label('Tipo do item')
                            ->required()
                            ->options([

                                '0' => 'Mercadoria',
                                '1' => 'Matéria Prima',
                                '2' => 'Produto Intermediário',
                                '3' => 'Produto em Fabricação',
                                '4' => 'Produto Acabado',
                                '5' => 'Embalagem',
                                '6' => 'Subproduto',
                                '7' => 'Material de Uso e Consumo',
                                '8' => 'Ativo Imobilizado',
                                '9' => 'Serviços',
                                '10' => 'Outros Insumos',
                                '99' => 'Outros',

                            ])
                            ->columnSpan(1),

                        ColorPicker::make('color')
                            ->label('Cor')
                            ->columnSpan(1),

                        Toggle::make('is_difal')
                            ->label('Difal')
                            ->default(false)
                            ->required()
                            ->columnSpan(1),

                        Toggle::make('is_devolucao')
                            ->label('Devolução')
                            ->default(false)
                            ->required()
                            ->columnSpan(1),

                        Toggle::make('is_enable')
                            ->label('Habilitado')
                            ->default(true)
                            ->required()
                            ->columnSpan(1),

                    ])
                    ->columnSpanFull()
                    ->columns(3),
            ]);
    }
}
