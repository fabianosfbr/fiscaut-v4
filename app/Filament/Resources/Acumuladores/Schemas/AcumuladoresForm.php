<?php

namespace App\Filament\Resources\Acumuladores\Schemas;

use App\Models\Acumulador;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

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
                                modifyRuleUsing: fn($rule) =>
                                $rule->where('issuer_id', Auth::user()->issuer_id)
                            )
                            ->validationMessages([
                                'unique' => 'O código acumulador já está em uso.',
                            ]),
                        TextInput::make('nome_acu')
                            ->label('Nome')

                    ])->columns(2),
            ]);
    }
}
