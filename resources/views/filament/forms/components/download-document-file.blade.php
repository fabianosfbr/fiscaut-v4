<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{ state: $wire.$entangle(@js($getStatePath())) }"
        {{ $getExtraAttributeBag() }}
    >
        <x-filament::link
            icon="heroicon-m-arrow-down-tray"
            x-bind:href="state
                ? '{{ route('upload-file.preview', ['id' => '__ID__']) }}'.replace('__ID__', state)
                : '#'"
            x-bind:target="state ? '_blank' : null"
            x-bind:rel="state ? 'noopener noreferrer' : null"
        >
            Download
        </x-filament::link>
    </div>
</x-dynamic-component>