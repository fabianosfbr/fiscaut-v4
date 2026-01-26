<?php

namespace App\Filament\Resources\SimplesNacionalAnexos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SimplesNacionalAnexoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Anexo')
                    ->schema([
                        Select::make('anexo')
                            ->label('Anexo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->searchable()
                            ->preload()
                            ->options([
                                'I' => 'Anexo I - Comércio',
                                'II' => 'Anexo II - Indústria',
                                'III' => 'Anexo III - Serviços e Locação de Bens Móveis',
                                'IV' => 'Anexo IV - Serviços',
                                'V' => 'Anexo V - Serviços',
                            ])
                            ->helperText('Selecione o anexo do Simples Nacional conforme a atividade')
                            ->placeholder('Escolha um anexo')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if (! in_array($value, ['I', 'II', 'III', 'IV', 'V'])) {
                                            $fail('O anexo selecionado não é válido.');
                                        }
                                    };
                                },
                            ])
                            ->columnSpanFull(),
                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(500)
                            ->minLength(10)
                            ->rows(4)
                            ->helperText('Descrição detalhada do anexo (mínimo 10, máximo 500 caracteres)')
                            ->columnSpanFull()
                            ->rules([
                                'required',
                                'string',
                                'min:10',
                                'max:500',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if (empty(trim($value))) {
                                            $fail('A descrição não pode conter apenas espaços em branco.');
                                        }

                                        // Verificar se contém pelo menos algumas palavras significativas
                                        $words = str_word_count(trim($value));
                                        if ($words < 3) {
                                            $fail('A descrição deve conter pelo menos 3 palavras.');
                                        }
                                    };
                                },
                            ]),
                        Toggle::make('ativo')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Define se este anexo está ativo para uso no sistema')
                            ->inline(false),
                    ])->columnSpanFull(),
            ]);
    }
}
