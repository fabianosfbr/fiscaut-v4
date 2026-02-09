<?php

namespace App\Filament\Resources\Clientes\Schemas;

use App\Filament\Forms\Components\SelectPlanoDeConta;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do cliente')
                    ->schema([
                        TextInput::make('nome')
                            ->required(),
                        TextInput::make('cnpj')
                            ->label('CNPJ'),
                        SelectPlanoDeConta::make('conta_contabil')
                            ->label('Conta contabil')
                            ->required()
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
