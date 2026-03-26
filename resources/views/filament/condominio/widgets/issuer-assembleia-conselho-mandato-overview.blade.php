<x-filament-widgets::widget>
    <x-filament::section>
        <div class="text-lg font-semibold py-3">Status do Prazo Técnico Mandato Conselho</div>
        <div class="space-y-2">
            @foreach ($items as $item)
                <div
                    class="flex items-center justify-between rounded px-3 py-2 text-sm font-semibold"
                    style="background-color: {{ $item['color'] }}; color: #ffffff;"
                >
                    <span>{{ $item['label'] }}</span>
                    <span>{{ $item['count'] }}</span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>