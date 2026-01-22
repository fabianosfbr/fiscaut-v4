<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados Básicos')
                    ->components([
                        TextInput::make('name')->required(),
                        TextInput::make('cnpj'),
                        TextInput::make('razao_social'),
                        TextInput::make('phone'),
                        Select::make('active')->options(['Y' => 'Ativo', 'N' => 'Inativo'])->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
