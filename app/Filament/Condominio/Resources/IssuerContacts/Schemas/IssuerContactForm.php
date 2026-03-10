<?php

namespace App\Filament\Condominio\Resources\IssuerContacts\Schemas;

use App\Enums\IssuerContactRoleEnum;
use App\Rules\CpfRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IssuerContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('nome')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('cpf')
                            ->label('CPF')
                            ->rules([new CpfRule])
                            ->mask('999.999.999-99')
                            ->placeholder('000.000.000-00'),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('telefone_whatsapp')
                            ->label('Telefone/WhatsApp')
                            ->tel()
                            ->mask('(99) 99999-9999')
                            ->placeholder('(00) 00000-0000')
                            ->maxLength(20),
                        TextInput::make('unidade')
                            ->maxLength(255),
                        Select::make('funcao')
                            ->label('Função')
                            ->options(function () {
                                $issuer = currentIssuer();

                                if (!$issuer) {
                                    return [];
                                }

                                return IssuerContactRoleEnum::getOptions($issuer->issuer_type);
                            })
                            ->required(),
                        Select::make('tipo_relacao')
                            ->label('Tipo de Relação')
                            ->options([
                                'isencao' => 'Isenção',
                                'remuneracao' => 'Remuneração',
                            ])
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
