@php
    $tagged = $record?->tagged ?? collect();
    $maxVisible = $column->getMaxVisible();
    $visibleTagged = $tagged->take($maxVisible);
    $remaining = max($tagged->count() - $maxVisible, 0);
@endphp

<div>
    @if ($tagged->isEmpty())
        <span class="text-sm text-gray-500">
            {{ $column->getEmptyText() }}
        </span>
    @else
        <div class="flex flex-wrap gap-1">
            @foreach ($visibleTagged as $taggedItem)
                <span class="relative inline-block" x-data>
                    <template x-ref="tooltipTemplate">
                        <div class="p-2">
                            <p class="text-sm font-normal text-left text-gray-900 whitespace-nowrap">
                                {{ $taggedItem->tag_name }} - R$ {{ formatar_moeda($taggedItem->value) }}
                            </p>
                        </div>
                    </template>

                    <x-filament::badge
                        x-tooltip="{
                            content: () => $refs.tooltipTemplate.innerHTML,
                            allowHTML: true,
                            maxWidth: 9999,
                            theme: 'light',
                            interactive: true,
                            animation: 'shift-away-subtle',
                            delay: [200, 50],
                            classList: 'popover',
                            appendTo: $root
                        }">
                        @if ($column->getShowTagCode() && filled($taggedItem->tag?->code))
                            {{ $taggedItem->tag->code }}
                        @else
                            {{ getLabelTag($taggedItem->tag_name) }}
                        @endif
                    </x-filament::badge>
                </span>
            @endforeach

            @if ($remaining > 0)
                <span class="inline-flex items-center" x-data>
                    <template x-ref="tooltipTemplate">
                        <div class="p-3">
                            @foreach ($tagged as $taggedItem)
                                <p class="text-sm font-normal text-left text-gray-900 whitespace-nowrap">
                                    {{ filled($taggedItem->tag?->code) ? $taggedItem->tag->code : getLabelTag($taggedItem->tag_name) }}
                                    - {{ $taggedItem->tag_name }} - R$ {{ formatar_moeda($taggedItem->value) }}
                                </p>
                            @endforeach
                        </div>
                    </template>

                    <span
                        x-tooltip="{
                            content: () => $refs.tooltipTemplate.innerHTML,
                            allowHTML: true,
                            maxWidth: 9999,
                            theme: 'light',
                            interactive: true,
                            animation: 'shift-away-subtle',
                            delay: [200, 50],
                            classList: 'popover',
                            appendTo: $root
                        }"
                    >
                        <x-filament::badge color="gray">
                            +{{ $remaining }} mais
                        </x-filament::badge>
                    </span>
                </span>
            @endif
        </div>
    @endif
</div>
