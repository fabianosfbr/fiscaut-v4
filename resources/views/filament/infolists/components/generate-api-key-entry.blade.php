<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div {{ $getExtraAttributeBag() }}>
        <div>
            <x-filament::button wire:loading.target="generateApiToken"
                wire:click="generateApiToken">Novo</x-filament::button>
        </div>
</x-dynamic-component>
