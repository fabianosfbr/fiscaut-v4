<?php

namespace App\Filament\Condominio\Resources\IssuerGroupControls\Schemas;

use Dom\Text;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IssuerGroupControlForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make("name")
                    ->label("Nome")
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make("description")
                    ->label("Descrição")
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }
}
