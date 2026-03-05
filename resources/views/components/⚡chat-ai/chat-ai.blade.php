<div x-data="{ open: @entangle('isOpen') }" x-effect="if (open) { $nextTick(() => $refs.input?.focus()) }"
    class="fixed bottom-4 right-4 z-[60] flex flex-col items-end gap-3">
    <div x-show="open" x-transition.opacity class="w-[92vw] max-w-[560px] sm:w-[460px]" style="display: none;">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Assistente IA
                </div>

                <div class="flex items-center gap-2">
                    <x-filament::icon-button icon="heroicon-m-arrow-path" color="gray" size="sm"
                        wire:click="resetConversation" wire:loading.attr="disabled"
                        wire:target="send,resetConversation" />

                    <x-filament::icon-button icon="heroicon-m-x-mark" color="gray" size="sm"
                        wire:click="close" />
                </div>
            </div>

            @if ($error)
                <div
                    class="border-b border-gray-200 bg-red-50 px-4 py-2 text-xs text-red-700 dark:border-gray-800 dark:bg-red-950/30 dark:text-red-200">
                    {{ $error }}
                </div>
            @endif

            <div class="max-h-[70vh] overflow-y-auto px-4 py-3 sm:h-[560px]">
                <div class="space-y-3">
                    @foreach ($messages as $message)
                        <div
                            class="{{ ($message['role'] ?? null) === 'user' ? 'flex justify-end' : 'flex justify-start' }}">
                            <div
                                class="{{ ($message['role'] ?? null) === 'user' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-gray-100' }} max-w-[85%] rounded-xl px-3 py-2 text-sm">
                                <div class="whitespace-pre-wrap">{{ $message['content'] ?? '' }}</div>

                                @if (!empty($message['at']))
                                    <div
                                        class="{{ ($message['role'] ?? null) === 'user' ? 'text-white/70' : 'text-gray-500 dark:text-gray-400' }} mt-1 text-[11px]">
                                        {{ \Illuminate\Support\Carbon::parse($message['at'])->format('H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div wire:loading.flex wire:target="send" class="flex justify-start">
                        <div
                            class="max-w-[85%] rounded-xl bg-gray-100 px-3 py-2 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            Processando…
                        </div>
                    </div>
                </div>
            </div>

            <form wire:submit="send" class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                <div class="flex items-end gap-2">
                    <textarea x-ref="input" rows="2"
                        class="block w-full resize-none rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 disabled:cursor-not-allowed disabled:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:disabled:bg-gray-950"
                        placeholder="Digite sua mensagem…" wire:model.live="input" wire:loading.attr="disabled" wire:target="send"
                        @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $wire.send(); }"></textarea>

                    <x-filament::button type="submit" size="sm" wire:loading.attr="disabled" wire:target="send"
                        :disabled="$isSending || blank($input)">
                        Enviar
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>



    <button wire:click="toggle" x-show="!open"
        class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        {{-- Chat icon --}}
        <svg  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="h-6 w-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
        </svg>

    </button>
</div>
