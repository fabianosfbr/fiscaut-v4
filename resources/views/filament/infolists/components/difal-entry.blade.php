<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div {{ $getExtraAttributeBag() }}>
        {{ $getState() }}

        <livewire:difal-table-infolist :difals="$record?->difal ?? []" />
    </div>
</x-dynamic-component>
