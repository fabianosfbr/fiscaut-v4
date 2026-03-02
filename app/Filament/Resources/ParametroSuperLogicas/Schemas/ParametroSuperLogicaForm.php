<?php

namespace App\Filament\Resources\ParametroSuperLogicas\Schemas;

use App\Filament\Forms\Components\SelectPlanoDeConta;
use App\Models\HistoricoContabil;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ParametroSuperLogicaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TagsInput::make('params')
                                    ->label('Parâmetros')
                                    ->placeholder('Insira o termo de busca')
                                    ->required()
                                    ->columnSpan(2),
                            ])->columnSpan(2),

                        Grid::make(2)
                            ->schema([
                                SelectPlanoDeConta::make('conta_credito')
                                    ->label('Conta crédito')
                                    ->required()
                                    ->columnSpan(1),
                                SelectPlanoDeConta::make('conta_debito')
                                    ->label('Conta débito')
                                    ->id('conta_debito')
                                    ->required()
                                    ->columnSpan(1),
                            ])->columnSpan(2),

                        Select::make('codigo_historico')
                            ->label('Cód. Histórico')
                            ->required()
                            ->options(function () {
                                $values = HistoricoContabil::where('issuer_id', currentIssuer()->id)
                                    ->orderBy('codigo', 'asc')
                                    ->get()
                                    ->map(function ($item) {
                                        $item->codigo_descricao = $item->codigo.' | '.$item->descricao;

                                        return $item;
                                    })

                                    ->pluck('codigo_descricao', 'codigo');

                                return $values;
                            })
                            ->columnSpan(2),
                        Toggle::make('check_value')
                            ->label('Verificar valor')
                            ->inline(false)
                            ->default(false)
                            ->onColor('success')
                            ->offColor('danger')
                            ->hint('Se marcado, o valor do registro sendo negativo, as contas crédito e débito serão invertidas.')
                            ->required()
                            ->columnSpan(1),

                    ])->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
