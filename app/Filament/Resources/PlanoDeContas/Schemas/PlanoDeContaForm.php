<?php

namespace App\Filament\Resources\PlanoDeContas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanoDeContaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plano de Contas')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código'),
                        TextInput::make('classificacao')
                            ->label('Classificação'),
                        TextInput::make('descricao')
                            ->label('Descrição'),
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'A' => 'Analítica',
                                'S' => 'Sintética',
                            ])
                            ->default('A'),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
