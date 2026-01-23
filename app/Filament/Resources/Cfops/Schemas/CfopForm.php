<?php

namespace App\Filament\Resources\Cfops\Schemas;

use App\Enums\SimplesNacionalReceitaEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CfopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do CFOP')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->required(),
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->required(),
                        Select::make('anexo')
                            ->label('Tributação')
                            ->required()
                            ->options(SimplesNacionalReceitaEnum::class)
                            ->columnSpan(1),
                        Select::make('is_faturamento')
                            ->label('Compõe Faturamento')
                            ->required()
                            ->options([
                                true => 'Sim',
                                false => 'Não',
                            ])
                            ->columnSpan(1),
                    ])->columnSpanFull(),
            ]);
    }
}
