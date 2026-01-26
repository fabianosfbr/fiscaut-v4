<?php

namespace App\Filament\Resources\CodigosServicos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CodigoServicoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Código de Serviço')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),
                        Select::make('cnae_id')
                            ->label('CNAE')
                            ->relationship('cnae', 'descricao')
                            ->searchable()
                            ->preload(),
                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
