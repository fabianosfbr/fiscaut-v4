<x-filament::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 16px;" class="flex items-center gap-4">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save" :disabled="!Auth::user()->currentIssuer || $isLoading">

                <span wire:loading.remove wire:target="save">
                    {{ $isLoading ? 'Importando...' : 'Importar documentos' }}
                </span>

                <span wire:loading wire:target="save">
                    Importando...
                </span>
            </x-filament::button>

            <x-filament::button type="button" color="gray" tag="a"
                href="{{ route('filament.app.resources.xml-import-history.index') }}">
                Ver Importações
            </x-filament::button>
        </div>
    </form>

    <x-filament::section heading="Sobre o processamento em segundo plano" class="mt-6">
        <p class="text-gray-500 dark:text-gray-400">
            Os arquivos XML são processados em segundo plano, permitindo que você continue usando o sistema enquanto o
            processamento ocorre.
            Você pode acompanhar o progresso das importações na página de <a
                href="{{ route('filament.app.resources.xml-import-history.index') }}"
                class="text-primary-600 hover:text-primary-500 dark:text-primary-500 dark:hover:text-primary-400">Histórico
                de Importações</a>.
        </p>
        <ul class="list-disc list-inside mt-2 text-gray-500 dark:text-gray-400">
            <li>Os arquivos são processados de forma assíncrona</li>
            <li>Você receberá uma notificação quando o processamento for concluído</li>
            <li>Você pode enviar múltiplos lotes de arquivos simultaneamente</li>
        </ul>
    </x-filament::section>
</x-filament::page>
