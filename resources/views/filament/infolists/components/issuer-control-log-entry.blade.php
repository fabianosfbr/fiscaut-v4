<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry">
    <div {{ $getExtraAttributeBag() }}>
        {{ $getState() }}
        @php
        $items = $record->logs()
        ->with('user')
        ->orderByDesc('created_at')
        ->limit(20)
        ->get();

        @endphp

        <ol class="border-s border-neutral-300 dark:border-neutral-500">

            @foreach ($items as $item)
            <li>
                <div class="flex-start flex items-center pt-3">
                    <div
                        class="-ms-[5px] me-3 h-[9px] w-[9px] rounded-full bg-neutral-300 dark:bg-neutral-500"></div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-300">
                        {{ $item->created_at->format('d/m/Y') }}
                    </p>
                </div>
                <div class="mb-6 ms-4 mt-2">
                    <p class="mb-1.5 text-md font-semibold">Alteração realizada por {{ e($item->usuario?->name ?? 'Sistema') }}</p>
                    <p class="mb-3 text-neutral-500 dark:text-neutral-300">
                        {{ $item->observacao }}
                    </p>
                </div>
            </li>
            @endforeach



        </ol>
    </div>
</x-dynamic-component>