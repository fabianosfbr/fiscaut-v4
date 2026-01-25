<?php

namespace App\Filament\Resources\SimplesNacionalAnexos\RelationManagers;

use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class AliquotasRelationManager extends RelationManager
{
   protected static string $relationship = 'aliquotas';

    protected static ?string $title = 'Alíquotas';

    protected static ?string $modelLabel = 'Alíquota';

    protected static ?string $pluralModelLabel = 'Alíquotas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('faixa_inicial')
                            ->label('Faixa Inicial (R$)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('R$')
                            ->helperText('Valor inicial da faixa de receita bruta')
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if ($value < 0) {
                                            $fail('A faixa inicial não pode ser negativa.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('faixa_final')
                            ->label('Faixa Final (R$)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('R$')
                            ->helperText('Valor final da faixa de receita bruta')
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $faixaInicial = request()->input('faixa_inicial');
                                        if ($faixaInicial && $value <= $faixaInicial) {
                                            $fail('A faixa final deve ser maior que a faixa inicial.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('aliquota')
                            ->label('Alíquota (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Percentual da alíquota aplicável')
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                'max:100',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if ($value < 0 || $value > 100) {
                                            $fail('A alíquota deve estar entre 0% e 100%.');
                                        }
                                        
                                        // Validação específica do Simples Nacional
                                        if ($value > 33.5) {
                                            $fail('A alíquota não pode exceder 33,5% conforme legislação do Simples Nacional.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('valor_deduzir')
                            ->label('Valor a Deduzir (R$)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('R$')
                            ->helperText('Valor a ser deduzido no cálculo do imposto')
                            ->default(0)
                            ->rules([
                                'nullable',
                                'numeric',
                                'min:0',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        if ($value !== null && $value < 0) {
                                            $fail('O valor a deduzir não pode ser negativo.');
                                        }
                                    };
                                },
                            ]),
                    
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('faixa_inicial')
            ->defaultSort('faixa_inicial')
            ->striped()
            ->columns([
                TextColumn::make('faixa_inicial')
                    ->label('Faixa Inicial')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('faixa_final')
                    ->label('Faixa Final')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('aliquota')
                    ->label('Alíquota')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('valor_deduzir')
                    ->label('Valor a Deduzir')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nova Alíquota'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
                DeleteAction::make()
                    ->label('Excluir'),
            ]);
    }
}
