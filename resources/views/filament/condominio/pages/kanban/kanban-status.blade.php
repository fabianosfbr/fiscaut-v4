@props(['status'])

<div class="md:w-[24rem] flex-shrink-0 mb-5 md:min-h-full flex flex-col">
    @include('filament.condominio.pages.kanban.kanban-header')

    <div data-status-id="{{ $status['id'] }}"
        class="flex flex-col flex-1 gap-2 p-3 bg-gray-200 dark:bg-gray-800 rounded-xl">
        @foreach ($status['records'] as $record)
            @include('filament.condominio.pages.kanban.kanban-record')
        @endforeach
    </div>
</div>
