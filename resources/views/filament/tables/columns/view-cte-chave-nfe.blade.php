<p class="text-sm bg-gray-100 rounded-lg" style="position: relative;">
    @php
        $chaves = $getRecord()->nfe_chave;
    @endphp

    @if (!is_null($chaves))
        @foreach ($chaves as $key => $chave)
            @if (is_string($chave) && $key == 'chave')
                <div x-data="{ tooltip: '{{ $chave }}' }" style="display: flex; justify-content: center; width: 100%;">
                    <x-filament::icon-button icon="heroicon-o-key"
                        x-tooltip.placement.left.max-width.500.on.click.interactive.debounce.250="tooltip" />
                </div>
            @else
                @if (is_array($chave))
                    @foreach ($chave as $key => $value)
                        @if ($key == 'chave')
                            <div x-data="{ tooltip: '{{ $value }}' }" style="display: flex; justify-content: center; width: 100%;">
                                <x-filament::icon-button icon="heroicon-o-key"
                                    x-tooltip.placement.left.max-width.500.on.click.interactive.debounce.250="tooltip" />
                            </div>
                        @endif
                    @endforeach
                @endif
            @endif
        @endforeach
    @endif
</p>
