<div>
    @if ($isVisible)
        <div @if ($poll) wire:poll.2s="loadProgress" @endif>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">

                {{-- Mensagem --}}
                <div class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ $message ?? 'Aguardando processamento...' }}
                </div>

                {{-- Barra de progresso --}}
                <div class="relative h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="
                    absolute left-0 top-0 h-full rounded-full transition-all duration-500
                    {{ $status === 'done' ? 'bg-emerald-500' : '' }}
                    {{ $status === 'failed' ? 'bg-red-500' : '' }}
                    {{ $status === 'running' ? 'bg-primary-600' : '' }}
                "
                        style="width: {{ $progress }}%;"></div>
                </div>

                {{-- Percentual --}}
                <div class="mt-1 text-right text-xs text-gray-500 dark:text-gray-400">
                    {{ $progress }}%
                </div>

                {{-- Status --}}
                <div
                    class="mt-1 text-xs font-medium
            {{ $status === 'done' ? 'text-emerald-600' : '' }}
            {{ $status === 'failed' ? 'text-red-600' : '' }}
            {{ $status === 'running' ? 'text-primary-600' : '' }}
        ">

                    @if ($status === 'running')
                        <span>
                            Em execução
                        </span>
                    @endif
                    @if ($status === 'pending')
                        <span>
                            Pendente
                        </span>
                    @endif
                    @if ($status === 'done')
                        <span>
                            Concluído
                        </span>
                    @endif
                    @if ($status === 'failed')
                        <span>
                            Falhou
                        </span>
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>
