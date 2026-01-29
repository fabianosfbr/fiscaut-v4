<div {{ $getExtraAttributeBag() }} style="display: flex; justify-content: center; width: 100%;">
    <div x-data="{ tooltip: '{{ $getState() }}' }">        
        <x-filament::icon-button icon="heroicon-o-key" x-tooltip.placement.left.max-width.500.on.click.interactive.debounce.250="tooltip" />
    </div>
</div>