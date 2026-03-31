<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <p class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-3">
                    Vencimentos dos Controles
                </p>
                <div class="space-y-3 ">
                    <!-- Item -->
                    <div class="flex items-center gap-3">
                        <span
                            class="bg-[#f5130b] text-white text-sm font-bold px-3 py-1 rounded-md min-w-[40px] text-center">
                            {{ $vencidos }}
                        </span>
                        <x-filament::link :href="route('filament.condominio.resources.issuer-controls.index', [
                            'activeTableView' => 'overdue_controls',
                        ])">
                            Vencidos
                        </x-filament::link>
                    </div>

                    <!-- Item -->
                    <div class="flex items-center gap-3">
                        <span
                            class="bg-[#f59e0b] text-white text-sm font-bold px-3 py-1 rounded-md min-w-[40px] text-center">
                            {{ $proximos7 }}
                        </span>
                        <x-filament::link :href="route('filament.condominio.resources.issuer-controls.index', [
                            'activeTableView' => 'overdue_7days_controls',
                        ])">
                            Próximos 7 dias
                        </x-filament::link>
                    </div>

                    <!-- Item -->
                    <div class="flex items-center gap-3">
                        <span
                            class="bg-[#3b82f6] text-white text-sm font-bold px-3 py-1 rounded-md min-w-[40px] text-center">
                            {{ $proximos15 }}
                        </span>
                        <x-filament::link :href="route('filament.condominio.resources.issuer-controls.index', [
                            'activeTableView' => 'overdue_15days_controls',
                        ])">
                            Próximos 15 dias
                        </x-filament::link>
                    </div>

                    <!-- Item -->
                    <div class="flex items-center gap-3">
                        <span
                            class="bg-[#10b981] text-white text-sm font-bold px-3 py-1 rounded-md min-w-[40px] text-center">
                            {{ $proximos30 }}
                        </span>
                        <x-filament::link :href="route('filament.condominio.resources.issuer-controls.index', [
                            'activeTableView' => 'overdue_30days_controls',
                        ])">
                            Próximos 30 dias
                        </x-filament::link>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
