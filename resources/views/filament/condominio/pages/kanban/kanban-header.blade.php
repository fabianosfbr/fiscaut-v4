@php
    $iconColorClasses = match ($status['color'] ?? null) {
        'gray' => 'text-gray-400',
        'blue', 'info', 'primary' => 'text-blue-500',
        'green', 'success' => 'text-green-500',
        'warning' => 'text-amber-500',
        'danger' => 'text-red-500',
        default => 'text-primary-500',
    };
@endphp

<h3 class="mb-2 flex items-center gap-2 px-4 font-semibold text-lg text-gray-500 dark:text-gray-300">
    @if (filled($status['icon'] ?? null))
        <x-filament::icon
            :icon="$status['icon']"
            class="h-5 w-5 {{ $iconColorClasses }}"
        />
    @else
        <span class="{{ $iconColorClasses }}">❖</span>
    @endif

    <span>{{ $status['title'] }}</span>
</h3>
