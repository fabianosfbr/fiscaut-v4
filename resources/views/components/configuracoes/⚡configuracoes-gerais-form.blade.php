<?php

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Contracts\View\View;
use Filament\Schemas\Schema;
use Livewire\Component;

new class extends Component implements HasSchemas {
    use InteractsWithSchemas;

    public ?array $data = [];

    public bool $isLoading = false;

    public bool $hasChanges = false;

    protected static string $settingName = 'configuracoes_gerais';

    public function mount(): void
    {
        $this->loadCurrentSettings();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Checkbox::make('isNfeClassificarNaEntrada')->label('Data de Entrada na classificação da NFe')->helperText('Quando ativado, permite informar a data de entrada ao classificar uma NFe')->live()->afterStateUpdated(fn() => ($this->hasChanges = true)),

                Checkbox::make('isNfeManifestarAutomatica')->label('Manifestação automática pelo Fiscaut')->helperText('Quando ativado, o sistema realizará a manifestação automática das notas')->live()->afterStateUpdated(fn() => ($this->hasChanges = true)),

                Checkbox::make('isNfeClassificarSomenteManifestacao')->label('Classificação somente após manifestação')->helperText('Quando ativado, a classificação da NFe só será realizada após a manifestação')->live()->afterStateUpdated(fn() => ($this->hasChanges = true)),

                Checkbox::make('isNfeMostrarCodigoEtiqueta')->label('Mostrar código da etiqueta ao invés do nome abreviado')->helperText('Quando ativado, o sistema mostrará o código da etiqueta ao invés do nome abreviado')->live(onBlur: true)->afterStateUpdated(fn() => ($this->hasChanges = true)),

                Checkbox::make('isNfeTomaCreditoIcms')->label('Considerar como crédito de ICMS as NF com CFOP 1.401')->helperText('Quando ativado, o sistema considerará crédito de ICMS para notas com CFOP 1.401')->live()->afterStateUpdated(fn() => ($this->hasChanges = true)),

                Checkbox::make('verificar_uf_emitente_destinatario')->label('Verificar UF emitente X UF destinatário')->helperText('Quando ativado, verifica a UF do emitente e destinatário para processar os CFOPs corretamente')->live()->afterStateUpdated(fn() => ($this->hasChanges = true)),
            ])
            ->statePath('data');
    }

    protected function loadCurrentSettings(): void {}
};
?>

<div>
    <form wire:submit="save">
        {{ $this->form }}

        {{-- Barra de ações --}}
        <div style="padding-top: 15px;">
            <div class="flex items-center justify-between gap-4">

                <div class="flex items-center gap-3">


                    <x-filament::button type="submit" icon="heroicon-m-check" wire:loading.attr="disabled"
                        wire:target="save" :disabled="!Auth::user()->currentIssuer || $isLoading" color="{{ $hasChanges ? 'primary' : 'success' }}">

                        <span wire:loading.remove wire:target="save">
                            {{ $hasChanges ? 'Salvar Configurações' : 'Salvo' }}
                        </span>

                        <span wire:loading wire:target="save">
                            Salvando...
                        </span>
                    </x-filament::button>
                </div>
            </div>
        </div>
    </form>

    {{-- Ações modais --}}
    <x-filament-actions::modals />

    {{-- JavaScript para detectar mudanças não salvas --}}
    @script
        <script>
            // Avisa sobre mudanças não salvas ao sair da página
            window.addEventListener('beforeunload', function(e) {
                if ($wire.hasChanges) {
                    e.preventDefault();
                    e.returnValue = 'Você tem alterações não salvas. Deseja realmente sair?';
                    return e.returnValue;
                }
            });
        </script>
    @endscript

    {{-- Estilos customizados --}}
    <style>
        /* Animação suave para indicadores */
        .fi-btn {
            transition: all 0.2s ease-in-out;
        }

        /* Destaque para mudanças não salvas */
        .border-warning-200 {
            animation: pulse 2s infinite;
        }

        /* Loading state para o formulário */
        [wire\:loading] .fi-fo-component-ctn {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Sticky bar shadow */
        .sticky {
            box-shadow: 0 -1px 3px 0 rgb(0 0 0 / 0.1);
        }
    </style>
</div>
