<x-filament-widgets::widget>
    <x-filament::section>
        <div class="text-lg font-semibold py-3">Vencimentos dos Controles</div>
        <div class="grid grid-cols-1 gap-3">
            <div class="flex items-center justify-between rounded px-3 py-2 text-sm font-semibold">
                <span
                    style="display:inline-flex; align-items:center;
                    padding:0.125rem 0.5rem;font-size:0.75rem;font-weight:600;border-radius:9999px;
                    background-color: #f5130bff; color: #ffffff;">
                    Vencidos
                </span>
                <span>{{ $vencidos }}</span>
            </div>
            <div class="flex items-center justify-between rounded px-3 py-2 text-sm font-semibold">
                <span
                    style="display:inline-flex; align-items:center;
                    padding:0.125rem 0.5rem;font-size:0.75rem;font-weight:600;border-radius:9999px;
                    background-color: #f59e0b; color: #ffffff;">
                    Próximos 7 dias
                </span>
                <span>{{ $proximos7 }}</span>
            </div>
            <div class="flex items-center justify-between rounded px-3 py-2 text-sm font-semibold">
                <span
                    style="display:inline-flex; align-items:center;
                    padding:0.125rem 0.5rem;font-size:0.75rem;font-weight:600;border-radius:9999px;
                    background-color: #3b82f6; color: #ffffff;">
                    Próximos 15 dias
                </span>
                <span>{{ $proximos15 }}</span>
            </div>
            <div class="flex items-center justify-between rounded px-3 py-2 text-sm font-semibold">
                <span
                    style="display:inline-flex; align-items:center;
                    padding:0.125rem 0.5rem;font-size:0.75rem;font-weight:600;border-radius:9999px;
                    background-color: #10b981; color: #ffffff;">
                    Próximos 30 dias
                </span>
                <span>{{ $proximos30 }}</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
