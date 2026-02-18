<?php

namespace App\Filament\Pages\Configuracoes;

use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

class ConfiguracaoGeralPage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Configurações Gerais';

    protected static ?string $title = 'Configurações Gerais';

    protected static ?string $slug = 'configuracoes-gerais';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public ?array $data = [];

    protected string $view = 'filament.pages.configuracoes.configuracao-geral-page';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Geral')
                            ->schema([
                                Livewire::make('configuracao.configuracao-geral'),

                            ]),
                        Tab::make('Entrada')
                            ->schema([
                                Tabs::make('TiposEntrada')
                                    ->tabs([
                                        Tab::make('CFOPs')
                                            ->schema([
                                                Tabs::make('Tabs')
                                                    ->tabs([
                                                        Tabs\Tab::make('NFe')
                                                            ->schema([
                                                                Tabs::make('TiposNFes')
                                                                    ->tabs([
                                                                        Tabs\Tab::make('Notas de Terceiros')
                                                                            ->schema([
                                                                                Livewire::make('configuracao.cfop-nfe-entrada-terceiro'),
                                                                            ]),
                                                                        Tabs\Tab::make('Notas Próprias')
                                                                            ->schema([
                                                                                // Livewire::make('configuracao.cfop-nfe-entrada-propria'),
                                                                            ]),
                                                                    ]),
                                                            ]),
                                                        Tabs\Tab::make('CTe')
                                                            ->schema([
                                                                Tabs::make('TiposCTes')
                                                                    ->tabs([
                                                                        Tabs\Tab::make('Notas de Entrada')
                                                                            ->schema([
                                                                                // Livewire::make('configuracao.cfop-cte-nota-entrada'),
                                                                            ]),

                                                                        Tabs\Tab::make('Notas de Saida')
                                                                            ->schema([
                                                                                Livewire::make('configuracao.cfop-cte-nota-saida'),
                                                                            ]),
                                                                    ]),
                                                            ]),
                                                    ]),
                                            ]),
                                        Tab::make('Acumuladores')
                                            ->schema([
                                                Tabs::make('Tabs')
                                                    ->tabs([
                                                        Tabs\Tab::make('NFe')
                                                            ->schema([
                                                                Tabs::make('TiposNFes')
                                                                    ->tabs([
                                                                        Tabs\Tab::make('Notas de Terceiros')
                                                                            ->schema([
                                                                                Livewire::make('configuracao.acumulador-nfe-nota-terceiro'),
                                                                            ]),
                                                                        Tabs\Tab::make('Notas Próprias')
                                                                            ->schema([
                                                                                // Livewire::make('configuracao.acumulador-nfe-nota-propria'),
                                                                            ]),
                                                                    ]),
                                                            ]),
                                                        Tabs\Tab::make('CTe')
                                                            ->schema([
                                                                Tabs::make('TiposCTes')
                                                                    ->tabs([
                                                                        Tabs\Tab::make('Notas de Entrada')
                                                                            ->schema([
                                                                                //  Livewire::make('configuracao.acumulador-cte-nota-entrada'),
                                                                            ]),

                                                                        Tabs\Tab::make('Notas de Saida')
                                                                            ->schema([
                                                                                //  Livewire::make('configuracao.acumulador-cte-nota-saida'),
                                                                            ]),
                                                                    ]),
                                                            ]),
                                                    ]),
                                            ]),
                                        Tab::make('Impostos')
                                            ->schema([
                                                //  Livewire::make('configuracao.imposto-equivalente')
                                            ]),
                                        Tab::make('Produtos Genéricos')
                                            ->schema([
                                                //  Livewire::make('configuracao.produto-generico')
                                            ]),

                                    ]),

                            ]),

                    ]),
            ]);
    }
}
