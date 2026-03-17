<?php

namespace App\Filament\Condominio\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

class IssuerControl extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    protected string $view = 'filament.condominio.pages.issuer-control';

    protected static ?string $title = 'Visão Geral';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->vertical()
                    ->persistTab()
                    ->tabs([
                        Tab::make('Seguro')
                            ->schema([
                                Livewire::make('controle.seguro'),

                            ]),
                        Tab::make('AVCB')
                            ->schema([
                                Livewire::make('controle.avcb'),

                            ]),
                        Tab::make('Estanqueidade')
                            ->schema([
                                Livewire::make('controle.estanqueidade'),
                            ]),
                        Tab::make('SPDA')
                            ->schema([
                                Livewire::make('controle.spda'),

                            ]),
                        Tab::make('Laudo Elétrico')
                            ->schema([
                                Livewire::make('controle.laudo-eletrico'),
                            ]),
                        Tab::make('Piscina')
                            ->schema([
                                Livewire::make('controle.piscina'),

                            ]),
                        Tab::make('Brigada de Incêndio')
                            ->schema([
                                Livewire::make('controle.brigada-incendio'),

                            ]),
                        Tab::make('Manutenções Programadas')
                            ->schema([
                                Livewire::make('controle.manutencao-programada'),

                            ]),
                        Tab::make('Obrigações Acessórias')
                            ->schema([
                                Livewire::make('controle.obrigacao-acessoria'),

                            ]),
                    ]),
            ]);
    }
}
