<?php

namespace App\Filament\Resources\Fornecedors\Schemas;

use App\Filament\Forms\Components\SelectPlanoDeConta;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FornecedorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do fornecedor')
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
