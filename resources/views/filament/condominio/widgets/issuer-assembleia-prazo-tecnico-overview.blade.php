<x-filament-widgets::widget>
    <x-filament::section>

        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <p class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-3">
                    Prazo Técnico Edital
                </p>
                <div class="space-y-3">
                    @foreach ($items as $item)
                        <div class="flex items-center gap-3">
                            <span class="text-white text-sm font-bold px-3 py-1 rounded-md min-w-[40px] text-center"
                                style="background-color: {{ $item['color'] }}">
                                {{ $item['count'] }}
                            </span>
                            @if (filled($item['url']))
                                <x-filament::link :href="$item['url']">
                                    {{ $item['label'] }}
                                </x-filament::link>
                            @else
                                <span
                                    class="fi-color fi-color-primary fi-text-color-700 dark:fi-text-color-400 fi-size-md">{{ $item['label'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
