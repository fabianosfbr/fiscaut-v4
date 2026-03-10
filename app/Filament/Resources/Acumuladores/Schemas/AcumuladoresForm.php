<?php

namespace App\Filament\Resources\Acumuladores\Schemas;

use App\Models\Acumulador;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AcumuladoresForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Acumulador')
                    ->schema([
                        TextInput::make('codi_acu')
                            ->label('Código Acumulador')
                            ->required()
                            ->unique(
                                table: Acumulador::class,
                                column: 'codi_acu',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->where('issuer_id', currentIssuer()->id)
                            )
                            ->validationMessages([
                                'unique' => 'O código acumulador já está em uso.',
                            ]),
                        TextInput::make('nome_acu')
                            ->label('Nome'),

                    ])->columns(2),
            ]);
    }
}
