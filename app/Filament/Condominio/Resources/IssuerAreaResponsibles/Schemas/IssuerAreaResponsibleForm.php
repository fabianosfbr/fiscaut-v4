<?php

namespace App\Filament\Condominio\Resources\IssuerAreaResponsibles\Schemas;

use App\Enums\AreaAtendimentoEnum;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IssuerAreaResponsibleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('user_id')
                            ->label('Usuário')
                            ->relationship('user', 'name', function ($query) {
                                return $query->where('tenant_id', currentIssuer()->tenant_id);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('area')
                            ->label('Área de Atendimento')
                            ->multiple()
                            ->options(AreaAtendimentoEnum::class)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
