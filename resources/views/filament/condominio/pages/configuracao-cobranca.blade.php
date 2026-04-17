<x-filament-panels::page>
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
</x-filament-panels::page>
