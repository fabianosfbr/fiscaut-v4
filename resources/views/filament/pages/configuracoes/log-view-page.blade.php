<x-filament-panels::page>
    {{ $this->form }}
    <div class="fi-log-manager-toolbar">
        <div class="fi-log-manager-toolbar-content">
            {{ $this->content }}
        </div>
        @if (true)
            <div class="fi-log-manager-toolbar-actions">
                <x-filament::button
                    x-on:click="window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'filament-log-manager-delete-log-file-modal' } }));"
                    :disabled="is_null($this->logFile)" type="button" color="danger">
                    Deletar
                </x-filament::button>
            </div>
        @endif
        @if (true)
            <div class="fi-log-manager-toolbar-actions">
                <x-filament::button wire:click="download" :disabled="is_null($this->logFile)" type="button" color="primary">
                    Download
                </x-filament::button>
            </div>
        @endif
    </div>
    <hr class="fi-log-manager-divider">
    <div x-data="{ isCardOpen: null }" class="fi-log-manager-log-list">
        @forelse($this->getLogs() as $key => $log)
            <div class="fi-log-manager-log-card bg-{{ $log['level_class'] }}"
                :class="{ 'no-bottom-radius mb-0': isCardOpen == {{ $key }} }">
                <a @click="isCardOpen = isCardOpen == {{ $key }} ? null : {{ $key }} "
                    style="cursor: pointer;" class="fi-log-manager-log-summary">
                    <span>[{{ $log['date'] }}]</span>
                    {{ Str::limit($log['text'], 100) }}
                </a>
            </div>
            <div x-show="isCardOpen=={{ $key }}" class="fi-log-manager-log-details no-top-radius">
                <div>
                    <p>{{ $log['text'] }}</p>
                    @if (!empty($log['stack']))
                        <div class="fi-log-manager-log-stack">
                            <pre style="overflow-x: scroll;"><code>{{ trim($log['stack']) }}</code></pre>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <h3 class="fi-log-manager-empty-state">Nenhum log encontrado.</h3>
        @endforelse
    </div>
    <x-filament::modal id="filament-log-manager-delete-log-file-modal">
        <x-slot name="heading">
            Deletar Arquivo de Log
        </x-slot>
        <x-slot name="description">
            Tem certeza de que deseja deletar este arquivo de log?
        </x-slot>
        <x-slot name="footerActions">
            <x-filament::button type="button" x-on:click="isOpen = false" color="secondary" outlined="true"
                class="filament-page-modal-button-action">
                Cancelar
            </x-filament::button>
            <x-filament::button wire:click="delete" x-on:click="isOpen = false" type="button" color="danger"
                class="filament-page-modal-button-action">
                Confirmar
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
    <style>
        .fi-log-manager-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .fi-log-manager-toolbar-content {
            flex: 1 1 auto;
            margin-right: 0.5rem;
        }

        .fi-log-manager-toolbar-actions {
            flex: 0 0 auto;
            margin-left: 0.5rem;
        }

        .fi-log-manager-log-list {
            display: flex;
            flex-direction: column;
        }

        .fi-log-manager-log-card {
            position: relative;
            margin-bottom: 0.5rem;
            padding: 0.75rem;
            border-radius: 0.75rem;
        }

        .fi-log-manager-log-summary {
            display: block;
            overflow: hidden;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            color: #ffffff;
        }

        .fi-log-manager-log-details {
            margin-bottom: 0.5rem;
            padding: 1rem;
            border-radius: 0.75rem;
            background-color: #ffffff;
            color: #111827;
        }

        .dark .fi-log-manager-log-details {
            background-color: #374151;
            color: #ffffff;
        }

        .fi-log-manager-log-stack {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f3f4f6;
            font-size: 0.875rem;
            line-height: 1.25rem;
            opacity: 0.4;
        }

        .dark .fi-log-manager-log-stack {
            background-color: #111827;
        }

        .fi-log-manager-empty-state {
            text-align: center;
        }

        .bg-danger {
            background-color: rgb(185 28 28);
        }

        .bg-warning {
            background-color: rgb(251 191 36);
        }

        .bg-info {
            background-color: rgb(34 211 238);
        }

        .no-bottom-radius {
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .no-top-radius {
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        hr.fi-log-manager-divider {
            border: 0;
            border-top: 1px solid #e5e7eb;
        }

        .dark hr.fi-log-manager-divider {
            border-color: #374151;
        }
    </style>
</x-filament-panels::page>
