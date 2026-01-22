<?php

namespace App\Filament\Pages\Configuracoes;

use App\Livewire\Configuracoes\ConfiguracoesGeraisForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorCteNfeEntradaForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorCteNfeSaidaForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorNfeNotasPropriaForm;
use App\Livewire\Configuracoes\Entrada\AcumuladorNfeNotasTerceiroForm;
use App\Livewire\Configuracoes\Entrada\CfopCteNotaEntradaForm;
use App\Livewire\Configuracoes\Entrada\CfopCteNotaSaidaForm;
use App\Livewire\Configuracoes\Entrada\CfopNfeEntradaPropriaForm;
use App\Livewire\Configuracoes\Entrada\CfopNfeEntradaTerceiroForm;
use App\Livewire\Configuracoes\Entrada\ImpostoEquivalenteForm;
use App\Livewire\Configuracoes\Entrada\ProdutosGenericosForm;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use UnitEnum;

class ConfiguracaoGeral extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Configurações Gerais';

    protected static ?string $title = 'Configurações Gerais';

    protected static ?string $slug = 'configuracoes-gerais';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public ?array $data = [];

    protected string $view = 'filament.pages.configuracoes.configuracao-geral';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Configurações')
                    ->persistTab()
                    ->tabs([
                        // Tab::make('Geral')
                        //     ->schema([
                        //         Livewire::make('configuracoes.configuracoes-gerais-form'),
                        //     ]),
                    ]),
            ]);
    }

}
