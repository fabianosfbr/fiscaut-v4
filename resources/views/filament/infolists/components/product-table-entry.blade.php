<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div {{ $getExtraAttributeBag() }}>
        {{ $getState() }}
        

        {{-- @dd($record->produtos) --}}

        <livewire:product-table-infolist :products="$record->produtos" />
    </div>
</x-dynamic-component>
