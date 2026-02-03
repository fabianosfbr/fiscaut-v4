<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <x-filament::section class="lg:col-span-4">
            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                Relatório Entrada vs Saída
            </div>

            <div class="mt-4">
                <div class="grid grid-cols-4 items-center gap-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <div>Data Ref.</div>
                    <div class="text-right">Entradas</div>
                    <div class="text-right">Saídas</div>
                    <div class="text-right">Resultado</div>
                </div>

                <div class="mt-2 divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-700 dark:bg-transparent">
                    @forelse ($rows as $row)
                        <div class="grid grid-cols-4 items-center gap-3 px-4 py-2.5 text-sm">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $row['data_ref'] }}
                            </div>
                            <div class="text-right font-medium text-primary-600 dark:text-primary-400">
                                R$ {{ formatar_moeda($row['entradas']) }}
                            </div>
                            <div class="text-right font-medium text-rose-600 dark:text-rose-400">
                                R$ {{ formatar_moeda($row['saidas']) }}
                            </div>
                            <div @class([
                                'text-right font-semibold',
                                'text-red-600 dark:text-red-400' => ($row['resultado'] ?? 0) < 0,
                                'text-gray-900 dark:text-gray-100' => ($row['resultado'] ?? 0) >= 0,
                            ])>
                                R$ {{ formatar_moeda($row['resultado']) }}
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            Nenhum dado encontrado.
                        </div>
                    @endforelse

                    <div class="grid grid-cols-4 items-center gap-3 px-4 py-3 text-sm font-semibold">
                        <div class="text-gray-900 dark:text-gray-100">Total</div>
                        <div class="text-right text-gray-900 dark:text-gray-100">
                            R$ {{ formatar_moeda($totalEntradas) }}
                        </div>
                        <div class="text-right text-gray-900 dark:text-gray-100">
                            R$ {{ formatar_moeda($totalSaidas) }}
                        </div>
                        <div @class([
                            'text-right',
                            'text-red-600 dark:text-red-400' => $totalResultado < 0,
                            'text-gray-900 dark:text-gray-100' => $totalResultado >= 0,
                        ])>
                            R$ {{ formatar_moeda($totalResultado) }}
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section class="lg:col-span-8">
            @livewire(\App\Filament\Widgets\RelatorioEntradaSaidaChart::class)
        </x-filament::section>
    </div>
</x-filament-panels::page>

