<?php

namespace App\Filament\Resources\SimplesNacionalAliquotas\Schemas;

use App\Models\SimplesNacionalAnexo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SimplesNacionalAliquotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Alíquota')
                    ->schema([
                        Select::make('anexo')
                            ->label('Anexo')
                            ->required()
                            ->searchable()
                            ->options(function () {
                                return SimplesNacionalAnexo::query()
                                    ->where('ativo', true)
                                    ->orderBy('anexo')
                                    ->get()
                                    ->mapWithKeys(fn (SimplesNacionalAnexo $anexo) => [
                                        $anexo->anexo => "{$anexo->anexo} - {$anexo->descricao}",
                                    ])
                                    ->all();
                            })
                            ->columnSpanFull(),
                        TextInput::make('faixa_inicial')
                            ->label('Faixa inicial')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('faixa_final')
                            ->label('Faixa final')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->rule('gte:faixa_inicial'),
                        TextInput::make('aliquota')
                            ->label('Alíquota (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('valor_deduzir')
                            ->label('Valor a deduzir (R$)')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('Percentuais de distribuição (opcional)')
                    ->schema([
                        TextInput::make('irpj_percentual')
                            ->label('IRPJ (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('csll_percentual')
                            ->label('CSLL (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('cofins_percentual')
                            ->label('COFINS (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('pis_percentual')
                            ->label('PIS (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('cpp_percentual')
                            ->label('CPP (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('ipi_percentual')
                            ->label('IPI (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0),
                        TextInput::make('icms_percentual')
                            ->label('ICMS (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('iss_percentual')
                            ->label('ISS (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}
