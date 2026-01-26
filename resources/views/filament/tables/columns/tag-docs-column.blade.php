<div>
    <!-- Possui etiqueta atribuida então mostra -->
    @if (count($getRecord()->tagged) > 0)
        <div class="flex flex-wrap gap-1">
            @foreach ($getRecord()->tagged->take(2) as $tagged)
                <span x-data="{ bcolor: @js($tagged->tag->category->color) }" class="relative inline-block">
                    <template x-ref="template">
                        <div class="p-2">
                            <p class="text-sm font-normal text-left text-gray-900"
                                x-text="`{{ $tagged->tag_name }} - R$ {{ formatar_moeda($tagged->value) }}`"></p>
                        </div>
                    </template>
                    <x-filament::badge
                        x-tooltip="{
                                    content: () => $refs.template.innerHTML,
                                    allowHTML: true,
                                    maxWidth: 350,
                                    theme: 'light',
                                    interactive: true,
                                    animation: 'shift-away-subtle',
                                    delay: [200, 50],
                                    classList: 'popover',
                                    appendTo: $root
                                }">
                        @if ($getShowTagCode())
                            {{ $tagged->tag->code }}
                        @else
                            {{ getLabelTag($tagged->tag_name) }}
                        @endif

                    </x-filament::badge>
                </span>
            @endforeach
            @if (count($getRecord()->tagged) - 2 > 0)
                @php
                    $tagsValues = [];
                    $values = [];
                @endphp

                @foreach ($getRecord()->tagged as $tagged)
                    @php
                        $values['code'] = $tagged->tag->code;
                        $values['name'] = $tagged->tag_name;
                        $values['value'] = formatar_moeda($tagged->value);
                        array_push($tagsValues, $values);
                    @endphp
                @endforeach

                <span x-data="{ message: '', items: {{ @json_encode($tagsValues) }} }" class="inline-flex items-center">
                    <template x-ref="template">
                        <div class="p-3">
                            <template x-for="item in items">
                                <p class="text-sm font-normal text-left text-gray-900"
                                    x-text="`${item.code} - ${item.name} - R$ ${item.value}`"></p>
                            </template>
                        </div>
                    </template>
                    <span
                        x-tooltip="{
                            content: () => $refs.template.innerHTML,
                            allowHTML: true,
                            maxWidth: 350,
                            theme: 'light',
                            interactive: true,
                            animation: 'shift-away-subtle',
                            delay: [200, 50],
                            classList: 'popover',
                            appendTo: $root
                        }"
                        >
                        <span style="font-size: 10px;"> mais</span>
                    </span>
                </span>
            @endif
        </div>
    @endif
</div>
