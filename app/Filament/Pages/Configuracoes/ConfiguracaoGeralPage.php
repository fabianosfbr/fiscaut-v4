<?php

namespace App\Filament\Pages\Configuracoes;

use UnitEnum;
use Filament\Pages\Page;
use App\Livewire\CreatePost;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use App\Filament\Forms\Components\LivewireViewer;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use App\Livewire\Configuracao\ConfiguracaoGeralForm;
use App\Livewire\Configuracoes\ConfiguracoesGeraisForm;
use App\Livewire\Configuracoes\Entrada\CfopCteNotaSaidaForm;
use App\Livewire\Configuracoes\Entrada\ProdutosGenericosForm;
use App\Livewire\Configuracoes\Entrada\CfopCteNotaEntradaForm;
use App\Livewire\Configuracoes\Entrada\ImpostoEquivalenteForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorCteNfeSaidaForm;
use App\Livewire\Configuracoes\Entrada\CfopNfeEntradaPropriaForm;
use App\Livewire\Configuracoes\Entrada\CfopNfeEntradaTerceiroForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorCteNfeEntradaForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorNfeNotasPropriaForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorNfeNotasTerceiroForm;

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
                                Livewire::make('configuracao.configuracao-geral')

                            ]),
                        Tab::make('Entrada')
                            ->schema([
                                // Livewire::make('issuer-switcher')

                            ]),

                    ])
            ]);
    }
}
