@php
    $assignees = $record->assignees ?? collect();
    $visibleAssignees = $assignees->take(3);
    $remainingAssignees = $assignees->count() - $visibleAssignees->count();
    $dueDate = $record->due_date?->format('d/m H:i');
@endphp

<div id="{{ $record->getKey() }}" wire:click="recordClicked('{{ $record->getKey() }}')"
    class="record cursor-grab rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 shadow-sm transition hover:border-primary-300 hover:shadow-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
    @if ($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}, true) < 3) x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        " @endif>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h4 class="truncate text-base font-semibold text-gray-800 dark:text-white">
                    {{ $record->title }}
                </h4>

                @if ($record->urgent)
                    <x-filament::icon icon="heroicon-m-star" class="h-4 w-4 text-amber-400" />
                @endif
            </div>

            @if (filled($record->project))
                <div class="mt-1">
                    <span
                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-300">
                        {{ $record->project }}
                    </span>
                </div>
            @endif
        </div>

        @if (filled($dueDate))
            <div
                class="shrink-0 rounded-md bg-primary-50 px-2 py-1 text-xs font-semibold text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                {{ $dueDate }}
            </div>
        @endif
    </div>

    @if (filled($record->description))
        <p class="mt-3 text-sm leading-5 text-gray-500 dark:text-gray-400">
            {{ \Illuminate\Support\Str::limit($record->description, 110) }}
        </p>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3">
        <div class="flex items-center">
            @forelse ($visibleAssignees as $assignee)
                <x-filament-panels::avatar.user size="sm" :user="$assignee" />
            @empty
                <span
                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    Sem responsáveis
                </span>
            @endforelse

            @if ($remainingAssignees > 0)
                <div
                    class="-ml-2 flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-gray-100 text-xs font-semibold text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    +{{ $remainingAssignees }}
                </div>
            @endif
        </div>

        @if ($assignees->isNotEmpty())
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">
                {{ $assignees->count() }} {{ \Illuminate\Support\Str::plural('responsável', $assignees->count()) }}
            </span>
        @endif
    </div>
</div>
