<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do usuário')
                    ->description('Informe o nome e e-mail do usuário. Será encaminhado uma mensagem autorizando seu acesso')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->columnSpan(2),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->unique(table: User::class, ignoreRecord: true)
                            ->columnSpan(2),
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->columnSpan(2),
                        Grid::make(1)
                            ->schema([
                                Select::make('roles')
                                    ->label('Grupos')
                                    ->multiple()
                                    ->preload()
                                    ->relationship('roles', 'name'),
                            ]),
                        // Grid::make(1)
                        //     ->schema([
                        //         Select::make('permissions')
                        //             ->disabled()
                        //             ->label('Permissões')
                        //             ->multiple()
                        //             ->preload()
                        //             ->relationship('permissions', 'name'),
                        //     ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
