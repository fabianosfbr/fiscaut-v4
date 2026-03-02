<div>
    <form wire:submit="save">
        {{ $this->form }}

        {{-- Barra de ações --}}
        <div style="padding-top: 15px;">
            <div class="flex items-center justify-between gap-4">

                <div class="flex items-center gap-3">


                    <x-filament::button type="submit" icon="heroicon-m-check" wire:loading.attr="disabled"
                        wire:target="save" :disabled="!currentIssuer() || $isLoading" color="{{ $hasChanges ? 'primary' : 'success' }}">

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
