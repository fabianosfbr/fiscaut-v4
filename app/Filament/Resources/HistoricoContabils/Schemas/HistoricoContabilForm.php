<?php

namespace App\Filament\Resources\HistoricoContabils\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class HistoricoContabilForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->unique(modifyRuleUsing: function (Unique $rule, callable $get) {
                                return $rule
                                    ->where('issuer_id', Auth::user()->currentIssuer->id);
                            }, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Código já cadastrado',
                            ])
                            ->required(),
                        TextInput::make('descricao')
                            ->label('Descrição')
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
