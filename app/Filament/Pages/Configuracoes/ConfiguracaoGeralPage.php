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
                                // Livewire::make('issuer-switcher')

                            ]),

                    ]),
            ]);
    }
}
