<x-filament::page>
    <div x-data wire:ignore.self class="md:flex overflow-x-auto overflow-y-hidden gap-4 pb-4">

        @foreach ($statuses as $status)
            @include('filament.condominio.pages.kanban.kanban-status')
        @endforeach

        <div wire:ignore>
            @include('filament.condominio.pages.kanban.kanban-scripts')
        </div>

    </div>

    @unless ($disableEditModal)
        @include('filament.condominio.pages.kanban.edit-record-modal')
    @endunless


</x-filament::page>
