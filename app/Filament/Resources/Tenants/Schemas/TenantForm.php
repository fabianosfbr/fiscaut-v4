<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Enums\AtividadesEmpresariaisEnum;
use App\Enums\RegimesEmpresariaisEnum;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados Básicos')
                    ->components([
                        TextInput::make('razao_social')
                            ->label('Razão Social')
                            ->required()
                            ->columnSpan(1)
                            ->validationAttribute('razão social')
                            ->autofocus(),
                        TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->unique('tenants', 'cnpj', ignoreRecord: true)
                            ->required()
                            ->mask('99.999.999/9999-99')
                            ->validationAttribute('cnpj')
                            ->required()
                            ->columnSpan(1)
                            ->maxLength(255),
                        Select::make('regime')
                            ->columnSpan(1)
                            ->options(RegimesEmpresariaisEnum::class),
                        Select::make('atividade')
                            ->multiple()
                            ->columnSpan(1)
                            ->options(AtividadesEmpresariaisEnum::class),
                        Radio::make('contribuinte_icms')
                            ->label('Contribuinte ICMS?')
                            ->inline()
                            ->boolean(trueLabel: 'Sim', falseLabel: 'Não'),
                    ])
                    ->columnSpanFull(),

                Section::make('Dados de contato')
                    ->components([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->unique('users', 'email', ignoreRecord: true)
                            ->validationMessages(['unique' => 'O e-mail informado está em uso.'])
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Senha')
                            ->hint('para acessar a plataforma')
                            ->required()
                            ->revealable()
                            ->password()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn($state): bool => filled($state))
                            ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation')
                            ->autofocus(),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmar senha')
                            ->hint('para acessar a plataforma')
                            ->password()
                            ->revealable()
                            ->required()
                            ->visible(fn(Get $get): bool => filled($get('password')))
                            ->dehydrated(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
