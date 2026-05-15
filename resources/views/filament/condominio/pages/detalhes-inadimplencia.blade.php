<x-filament-panels::page>
    {{ $this->form }}

    <x-filament::section>
        <x-slot name="heading">
            Detalhes da Cobrança
        </x-slot>
        <livewire:listagem-inadimplenia-table :recebimentos="$record" />
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Notificações Enviadas
        </x-slot>
        <livewire:listagem-notificaco-cobranca-table :recebimentos="$record" />
    </x-filament::section>

</x-filament-panels::page>
