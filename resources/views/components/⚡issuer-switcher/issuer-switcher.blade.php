<div style="display: flex; align-items: center; gap: 12px; padding: 12px;">

    <!-- Formulário do Select -->
    <div style="flex: 1; min-width: 0;">
        <form wire:submit="submit" style="margin: 0;">
            {{ $this->form }}
        </form>
    </div>

    @if (canManageIssuers())

        <div style="flex-shrink: 0;">
            <x-filament::dropdown placement="bottom-end" width="sm">
                <x-slot name="trigger">
                    <x-filament::icon-button icon="heroicon-m-ellipsis-vertical" label="Ações da empresa" color="gray"
                        size="sm" />
                </x-slot>

                <x-filament::dropdown.list>
                    <!-- Nova Empresa -->
                    <x-filament::dropdown.list.item icon="heroicon-m-plus" icon-color="success" tag="a"
                        href="{{ route('filament.app.resources.issuers.create') }}">
                        Nova Empresa
                    </x-filament::dropdown.list.item>

                    <!-- Editar Empresa Atual -->
                    @if ($currentIssuer = Auth::user()->currentIssuer)
                        <x-filament::dropdown.list.item icon="heroicon-m-pencil-square" icon-color="primary"
                            tag="a"
                            href="{{ route('filament.app.resources.issuers.edit', ['record' => $currentIssuer->id]) }}">
                            Editar {{ \Illuminate\Support\Str::limit($currentIssuer->razao_social, 25) }}
                        </x-filament::dropdown.list.item>
                    @else
                        <x-filament::dropdown.list.item icon="heroicon-m-exclamation-triangle" icon-color="warning"
                            disabled>
                            Selecione uma empresa para editar
                        </x-filament::dropdown.list.item>
                    @endif
                </x-filament::dropdown.list>

                <!-- Gerenciar Todas as Empresas -->
                <x-filament::dropdown.list>
                    <x-filament::dropdown.list.item icon="heroicon-m-building-office" tag="a"
                        href="{{ route('filament.app.resources.issuers.index') }}">
                        Gerenciar Todas as Empresas
                    </x-filament::dropdown.list.item>
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        </div>

    @endif
</div>
