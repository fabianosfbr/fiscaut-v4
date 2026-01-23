<?php

use Livewire\Component;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;


new class extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        // 5. Documentação: Inicializa o formulário com o ID da empresa atual do usuário
        $this->form->fill([
            'issuer_id' => Auth::user()?->issuer_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('issuer_id')
                    ->hiddenLabel()
                    ->prefix('Empresa:')
                    ->placeholder('Selecione uma empresa')
                    // 1. Correção da Lógica de Carregamento:
                    // Filtra empresas vinculadas ao usuário logado via relacionamento
                    ->options(function () {
                        $user = Auth::user();

                        if (! $user) {
                            return [];
                        }

                        return $user->issuers()
                            ->wherePivot('active', true) // Garante que o vínculo está ativo
                            ->where('is_enabled', true)  // Garante que a empresa está ativa
                            ->pluck('razao_social', 'issuers.id');
                    })
                    // 2. Tratamento de Estados:
                    ->searchable() // Permite buscar na lista
                    ->preload()    // Carrega as opções antecipadamente (bom para UX se não houver milhares)
                    ->noSearchResultsMessage('Nenhuma empresa encontrada.')
                    ->searchingMessage('Buscando...')
                    ->live() // Reativo para mudanças imediatas
                    ->afterStateUpdated(function ($state) {
                        // Lógica de troca de contexto (Switching)
                        $user = Auth::user();

                        if ($user && $state) {
                            // Atualiza a empresa atual do usuário
                            $user->update([
                                'issuer_id' => $state
                            ]);

                            // Feedback visual
                            Notification::make()
                                ->title('Empresa alternada com sucesso!')
                                ->success()
                                ->duration(2000)
                                ->send();

                            // Recarrega a página para aplicar o contexto
                            return redirect(request()->header('Referer'));
                        }
                    })
            ]);
    }

    public function save(): void
    {
        // Método necessário para evitar erros caso o formulário seja submetido via "Enter"
        // A lógica principal ocorre no evento 'afterStateUpdated' do campo Select.
    }
};
