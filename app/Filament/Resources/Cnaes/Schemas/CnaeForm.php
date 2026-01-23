<?php

namespace App\Filament\Resources\Cnaes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class CnaeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do CNAE')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->required(),
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->required(),
                        Select::make('anexo')
                            ->label('Anexo')
                            ->options([
                                'I' => 'Anexo I',
                                'II' => 'Anexo II',
                                'III' => 'Anexo III',
                                'IV' => 'Anexo IV',
                                'V' => 'Anexo V',
                            ])
                            ->required(),
                        TextInput::make('aliquota')
                            ->label('Alíquota')
                            ->suffix('%')
                            ->mask(RawJs::make('$money($input, ",", ".", 2)'))
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2, ',', '.') : null)
                            ->dehydrateStateUsing(fn ($state) => $state ? (float) str_replace(['.', ','], ['', '.'], $state) : null)
                            ->required(),
                        Toggle::make('fator_r')
                            ->label('Fator R')
                            ->required(),

                    ])->columnSpanFull(),
            ]);
    }
}
