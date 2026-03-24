<?php

namespace App\Filament\Condominio\Resources\ManutencaoHistoricos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ManutencaoHistoricoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('manutencao_id')
                    ->relationship('manutencao', 'id')
                    ->required(),
                Select::make('usuario_id')
                    ->relationship('usuario', 'name')
                    ->required(),
                TextInput::make('acao')
                    ->required(),
                TextInput::make('status_anterior'),
                TextInput::make('status_novo'),
                Textarea::make('observacao')
                    ->columnSpanFull(),
                TextInput::make('dados_alterados'),
            ]);
    }
}
