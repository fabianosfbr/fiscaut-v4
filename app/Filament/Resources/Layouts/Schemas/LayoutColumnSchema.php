<?php

namespace App\Filament\Resources\Layouts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LayoutColumnSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('excel_column_name')
                    ->label('Nome da Coluna no Excel')
                    ->required()
                    ->maxLength(255),
                TextInput::make('target_column_name')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255),
                Select::make('data_type')
                    ->label('Tipo de Dado')
                    ->live()
                    ->options([
                        'text' => 'Texto',
                        'number' => 'Número',
                        'date' => 'Data',
                    ])
                    ->required(),
                TextInput::make('format')
                    ->label('Formato')
                    ->maxLength(255),

                Toggle::make('is_sanitize')
                    ->label('Limpar conteúdo')
                    ->default(false)
                    ->columnSpanFull(),
            ]);
    }
}
