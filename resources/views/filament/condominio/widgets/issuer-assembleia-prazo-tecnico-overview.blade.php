<x-filament-widgets::widget>
    <x-filament::section>
        <div class="text-lg font-semibold py-3">Status do Prazo Técnico Edital</div>
        <div class="space-y-2">
            @foreach ($items as $item)
                <div class="flex items-center justify-between rounded px-3 py-2 text-sm font-semibold">
                    <span
                        style="display:inline-flex; align-items:center;
                    padding:0.125rem 0.5rem;font-size:0.75rem;font-weight:600;border-radius:9999px;
                    background-color: {{ $item['color'] }}; color: #ffffff;">
                        {{ $item['label'] }}
                    </span>
                    <span>{{ $item['count'] }}</span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
