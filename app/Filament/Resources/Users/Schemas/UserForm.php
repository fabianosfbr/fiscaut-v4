<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpan(2),
                        Select::make('tenant_id')
                            ->label('Empresa')
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->disabledOn('edit')
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('roles', null))
                            ->required()
                            ->columnSpan(2),
                        Grid::make(1)
                            ->schema([
                                Select::make('roles')
                                    ->label('Grupos')
                                    ->multiple()
                                    ->preload()
                                    ->disabled(fn (Get $get) => ! $get('tenant_id'))
                                    ->relationship('roles', 'name', fn ($query, Get $get) => $query->where('tenant_id', $get('tenant_id'))->where('slug', '!=', 'super-admin')),

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
