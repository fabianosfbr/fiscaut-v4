<?php

namespace App\Filament\Resources\SimplesNacionalAliquotas\Schemas;


use Filament\Schemas\Schema;
use App\Models\SimplesNacionalAnexo;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class SimplesNacionalAliquotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Básicas')
                    ->description('Dados principais da alíquota do Simples Nacional')
                    ->schema([
                        Select::make('anexo')
                            ->label('Anexo')
                            ->options(
                                SimplesNacionalAnexo::ativo()
                                    ->pluck('descricao', 'anexo')
                                    ->map(fn($descricao, $anexo) => "Anexo {$anexo} - {$descricao}")
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Selecione o anexo do Simples Nacional')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('faixa_inicial')
                                    ->label('Faixa Inicial (R$)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('R$')
                                    ->helperText('Valor inicial da faixa de receita')
                                    ->rules([
                                        'required',
                                        'numeric',
                                        'min:0',
                                        function () {
                                            return function (string $attribute, $value, \Closure $fail) {
                                                $faixaFinal = request()->input('faixa_final');
                                                if ($faixaFinal && $value >= $faixaFinal) {
                                                    $fail('A faixa inicial deve ser menor que a faixa final.');
                                                }
                                            };
                                        },
                                    ]),

                                TextInput::make('faixa_final')
                                    ->label('Faixa Final (R$)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('R$')
                                    ->helperText('Valor final da faixa de receita')
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
                            ]),
                    ]),

                Section::make('Alíquotas e Valores')
                    ->description('Percentuais e valores para cálculo do Simples Nacional')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('aliquota')
                                    ->label('Alíquota (%)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(33.5)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Alíquota nominal da faixa (máx. 33,5%)')
                                    ->rules([
                                        'required',
                                        'numeric',
                                        'min:0',
                                        'max:33.5',
                                    ]),

                                TextInput::make('valor_deduzir')
                                    ->label('Valor a Deduzir (R$)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('R$')
                                    ->helperText('Valor a ser deduzido no cálculo')
                                    ->default(0)
                                    ->rules([
                                        'nullable',
                                        'numeric',
                                        'min:0',
                                    ]),
                            ]),
                    ]),

                Section::make('Detalhamento dos Impostos (%)')
                    ->description('Percentuais de cada imposto dentro da alíquota total')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('irpj_percentual')
                                    ->label('IRPJ (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual do IRPJ')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

                                TextInput::make('csll_percentual')
                                    ->label('CSLL (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual da CSLL')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

                                TextInput::make('cofins_percentual')
                                    ->label('COFINS (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual do COFINS')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

                                TextInput::make('pis_percentual')
                                    ->label('PIS (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual do PIS')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

                                TextInput::make('cpp_percentual')
                                    ->label('CPP (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual da Contribuição Previdenciária Patronal')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

                                TextInput::make('ipi_percentual')
                                    ->label('IPI (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual do IPI')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

                                TextInput::make('icms_percentual')
                                    ->label('ICMS (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual do ICMS')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

                                TextInput::make('iss_percentual')
                                    ->label('ISS (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->helperText('Percentual do ISS')
                                    ->rules(['nullable', 'numeric', 'min:0', 'max:100']),
                            ]),
                    ]),
            ]);
    }
}
