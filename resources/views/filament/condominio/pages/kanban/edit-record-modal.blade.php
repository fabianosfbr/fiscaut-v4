<x-filament::modal id="kanban--edit-record-modal" :slideOver="$this->getEditModalSlideOver()" :width="$this->getEditModalWidth()" :close-by-escaping="false"
    :close-by-clicking-away="false">

    <form wire:submit="save">
        <x-slot name="header">
            <x-filament::modal.heading>
                {{ $this->getEditModalTitle() }}
            </x-filament::modal.heading>
        </x-slot>
        {{ $this->form }}




        <div style="margin-top: 16px;" class="flex items-center gap-4">
            <x-filament::button type="submit">
                {{ $this->getEditModalSaveButtonLabel() }}
            </x-filament::button>

            <x-filament::button color="gray" x-on:click="isOpen = false">
                {{ $this->getEditModalCancelButtonLabel() }}
            </x-filament::button>
        </div>
    </form>
</x-filament::modal>
