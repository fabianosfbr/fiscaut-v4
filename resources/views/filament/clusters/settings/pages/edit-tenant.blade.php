<x-filament::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 16px;" class="flex items-center gap-4">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save">
                Salvar
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
