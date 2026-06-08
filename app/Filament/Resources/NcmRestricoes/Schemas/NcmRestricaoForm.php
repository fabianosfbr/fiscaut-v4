<?php

namespace App\Filament\Resources\NcmRestricoes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NcmRestricaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->schema([
                        TextInput::make('grupo')
                            ->label('Grupo')
                            ->required()
                            ->maxLength(20)
                            ->helperText('Ex: 110, MON-001, ISE-001')
                            ->columnSpan(1),
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(2),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Regra de validação')
                    ->schema([
                        Select::make('tipo')
                            ->label('Tipo de restrição')
                            ->required()
                            ->options([
                                'ALIQUOTA_ZERO' => 'Alíquota Zero',
                                'MONOFASICO' => 'Monofásico',
                                'SUSPENSAO' => 'Suspensão',
                                'ISENCAO' => 'Isenção',
                            ])
                            ->helperText('Define o CST resultante: ALIQUOTA_ZERO/MONOFASICO/SUSPENSAO/ISENCAO → CST 73')
                            ->columnSpan(1),

                        Select::make('tipo_match')
                            ->label('Tipo de correspondência')
                            ->required()
                            ->options([
                                'exato' => 'Exato (NCM completo)',
                                'prefixo' => 'Prefixo (início do NCM)',
                                'capitulo' => 'Capítulo (2 dígitos)',
                                'faixa_prefixo' => 'Faixa de prefixo',
                            ])
                            ->helperText('Como o NCM será comparado com os valor_match')
                            ->columnSpan(1),

                        TextInput::make('fundamento')
                            ->label('Fundamento legal')
                            ->maxLength(200)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Valores de correspondência')
                    ->schema([
                        Textarea::make('valor_match')
                            ->label('Valores para match')
                            ->required()
                            ->helperText('Separe por vírgula. Ex: 0401, 0402. Para faixa: [["1508","1514"],"1507"]')
                            ->formatStateUsing(
                                fn ($state): string => is_array($state)
                                    ? (json_encode($state, JSON_UNESCAPED_UNICODE))
                                    : (string) $state
                            )
                            ->dehydrateStateUsing(
                                fn ($state): array => json_validate($state)
                                    ? json_decode($state, true)
                                    : array_values(
                                        array_filter(
                                            array_map('trim', str_getcsv($state)),
                                            fn ($x) => $x !== '',
                                        ),
                                    )
                            )
                            ->columnSpan(2),

                        Textarea::make('excluir_ncm')
                            ->label('NCMs para excluir')
                            ->helperText('Separe por vírgula. Ex: 03029000, 04011000')
                            ->formatStateUsing(
                                fn ($state): string => is_array($state)
                                    ? (json_encode($state, JSON_UNESCAPED_UNICODE))
                                    : (string) $state
                            )
                            ->dehydrateStateUsing(
                                fn ($state): array => json_validate($state)
                                    ? json_decode($state, true)
                                    : array_values(
                                        array_filter(
                                            array_map('trim', str_getcsv($state)),
                                            fn ($x) => $x !== '',
                                        ),
                                    )
                            )
                            ->columnSpan(2),

                        Textarea::make('setores')
                            ->label('Setores')
                            ->helperText('Separe por vírgula. Ex: alimentos, combustiveis, industria')
                            ->formatStateUsing(
                                fn ($state): string => is_array($state)
                                    ? (json_encode($state, JSON_UNESCAPED_UNICODE))
                                    : (string) $state
                            )
                            ->dehydrateStateUsing(
                                fn ($state): array => json_validate($state)
                                    ? json_decode($state, true)
                                    : array_values(
                                        array_filter(
                                            array_map('trim', str_getcsv($state)),
                                            fn ($x) => $x !== '',
                                        ),
                                    )
                            )
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Observações')
                    ->schema([
                        Toggle::make('possui_ex')
                            ->label('Possui EX?')
                            ->helperText('Indica se o NCM possui exceção TIPI'),

                        Textarea::make('obs')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
