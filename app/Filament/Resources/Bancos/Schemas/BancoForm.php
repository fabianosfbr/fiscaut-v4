<?php

namespace App\Filament\Resources\Bancos\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use App\Filament\Forms\Components\SelectPlanoDeConta;

class BancoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do banco')
                    ->schema([
                        TextInput::make('nome')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->dehydrateStateUsing(function ($state) {
                                if ($state) {
                                    return sanitize($state);
                                }
                                return null;
                            })
                            ->columnSpan(1),
                        TextInput::make('agencia')
                            ->label('Agência')
                            ->required()
                            ->columnSpan(1)
                            ->dehydrateStateUsing(fn(string $state): string => (string) $state),
                        TextInput::make('conta')
                            ->label('Nº da Conta')
                            ->required()
                            ->columnSpan(1)
                            ->dehydrateStateUsing(fn(string $state): string => (string) $state),
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
