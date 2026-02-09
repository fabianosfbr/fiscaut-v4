<?php

namespace App\Filament\Resources\Layouts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class LayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->components([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->maxLength(255)
                            ->columnSpanFull()
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
