<?php

namespace App\Filament\Resources\ParametroGerals\Schemas;

use Filament\Schemas\Schema;
use App\Models\HistoricoContabil;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Forms\Components\SelectPlanoDeConta;
use Filament\Schemas\Components\Section;

class ParametroGeralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TagsInput::make('params')
                            ->label('Parâmetros')
                            ->placeholder('Insira o termo de busca')
                            ->required()
                            ->columnSpan(2),

                        ToggleButtons::make('is_inclusivo')
                            ->label('Forma que será aplicado o filtro')
                            ->required()
                            ->default(false)
                            ->options([
                                '0' => 'OU',
                                '1' => 'E',
                            ])
                            ->inline()
                            ->columnSpan(1),

                        SelectPlanoDeConta::make('conta_contabil')
                            ->label('Conta contabil')
                            ->required()
                            ->columnSpan(2),

                        Select::make('codigo_historico')
                            ->label('Cód. Histórico')
                            ->required()
                            ->options(function () {
                                $values = HistoricoContabil::where('issuer_id', Auth::user()->currentIssuer->id)
                                    ->orderBy('codigo', 'asc')
                                    ->get()
                                    ->map(function ($item) {
                                        $item->codigo_descricao = $item->codigo . ' | ' . $item->descricao;
                                        return $item;
                                    })

                                    ->pluck('codigo_descricao', 'codigo');
                                return $values;
                            })
                            ->columnSpan(2),

                        Hidden::make('id')
                    ])
                    ->columns(3)
                    ->columnSpanFull(),                

            ]);
    }
}
