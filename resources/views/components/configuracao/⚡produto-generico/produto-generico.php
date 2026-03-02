<?php

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\EntradasProdutosGenerico;
use App\Models\GrupoEntradasProdutosGenerico;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public bool $isLoading = false;

    public bool $hasChanges = false;

    public function mount(): void
    {
        $this->loadCurrentSettings();
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'data.')) {
            $this->hasChanges = true;
        }
    }

    protected function loadCurrentSettings(): void
    {
        $currentIssuer = currentIssuer();

        if (! $currentIssuer) {
            $this->form->fill([]);

            return;
        }

        $grupos = GrupoEntradasProdutosGenerico::getAllCached($currentIssuer->id, $currentIssuer->tenant_id);

        $formData = $this->transformDatabaseToFormData($grupos);

        $this->form->fill([
            'itens' => $formData,
        ]);
    }

    private function transformDatabaseToFormData($grupos): array
    {
        if ($grupos->isEmpty()) {
            return [];
        }

        return $grupos->map(function ($grupo) {
            return [
                'tag_id' => $this->extractTagId($grupo->tags),
                'produtos' => $this->transformProdutos($grupo->produtos),
            ];
        })->toArray();
    }

    private function extractTagId($tags): ?int
    {
        if (empty($tags) || ! is_array($tags)) {
            return null;
        }

        return $tags[0] ?? null;
    }

    private function transformProdutos($produtos): array
    {
        if ($produtos->isEmpty()) {
            return [];
        }

        return $produtos->map(function ($produto) {
            return [
                'codigo' => $produto->cod_produto,
                'descricao' => $produto->descricao,
                'ncm' => $produto->ncm,
            ];
        })->toArray();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('itens')
                    ->hiddenLabel()
                    ->schema([
                        SelectTagGrouped::make('tag_id')
                            ->label('Etiqueta')
                            ->columnSpan(1)
                            ->required()
                            ->options(function () {
                                return CategoryTag::getAllEnabled(currentIssuer()->id);
                            }),

                        Repeater::make('produtos')
                            ->label('Produtos')
                            ->schema([
                                TextInput::make('codigo')
                                    ->label('Código do Produto')
                                    ->required()
                                    ->maxLength(20)
                                    ->columnSpan(1),

                                TextInput::make('descricao')
                                    ->label('Descrição')
                                    ->required()
                                    ->maxLength(120)
                                    ->columnSpan(1),

                                TextInput::make('ncm')
                                    ->label('NCM')
                                    ->required()
                                    ->maxLength(8)
                                    ->mask('99999999')
                                    ->placeholder('00000000')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->addActionLabel('Adicionar Produto')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->addActionLabel('Adicionar Etiqueta')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->validate();
        $formData = $this->form->getState();

        try {
            $this->isLoading = true;
            $currentIssuer = currentIssuer();

            if (! $currentIssuer) {
                throw new \Exception('Nenhum emissor selecionado.');
            }

            DB::transaction(function () use ($formData, $currentIssuer) {
                $this->clearExistingGroups($currentIssuer);
                $this->createNewGroups($formData, $currentIssuer);
            });

            // Invalida o cache ANTES de recarregar os dados
            GrupoEntradasProdutosGenerico::invalidateCache($currentIssuer->id, $currentIssuer->tenant_id);

            $this->hasChanges = false;
            $this->showSuccessNotification();
            $this->refreshFormData();
        } catch (\Exception $e) {
            Log::error('Erro ao salvar produtos genéricos: '.$e->getMessage());
            $this->showErrorNotification($e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    private function refreshFormData(): void
    {
        // Limpa o estado atual do formulário
        $this->data = [];

        // Recarrega os dados do banco
        $this->loadCurrentSettings();

        // Força a re-renderização do formulário
        $this->form->fill($this->data);

        // Dispara eventos para notificar mudanças
        $this->dispatch('refreshComponent');
        $this->dispatch('$refresh');
    }

    private function clearExistingGroups($currentIssuer): void
    {
        $grupo = GrupoEntradasProdutosGenerico::where('issuer_id', $currentIssuer->id)->get();
        foreach ($grupo as $item) {
            $this->clearExistingProducts($item->id);
            $item->delete();
        }
    }

    private function clearExistingProducts($grupoId): void
    {
        EntradasProdutosGenerico::where('grupo_id', $grupoId)
            ->delete();
    }

    private function createNewGroups(array $formData, $currentIssuer): void
    {
        if (empty($formData['itens'])) {
            return;
        }

        foreach ($formData['itens'] as $item) {
            $grupo = $this->createGroup($item, $currentIssuer);
            $this->createProductsForGroup($item, $grupo, $currentIssuer);
        }
    }

    private function createGroup(array $item, $currentIssuer): GrupoEntradasProdutosGenerico
    {
        return GrupoEntradasProdutosGenerico::create([
            'tenant_id' => $currentIssuer->tenant_id,
            'issuer_id' => $currentIssuer->id,
            'tags' => [$item['tag_id']],
        ]);
    }

    private function createProductsForGroup(array $item, GrupoEntradasProdutosGenerico $grupo, $currentIssuer): void
    {
        if (empty($item['produtos'])) {
            return;
        }

        foreach ($item['produtos'] as $produto) {
            EntradasProdutosGenerico::create([
                'tenant_id' => $currentIssuer->tenant_id,
                'grupo_id' => $grupo->id,
                'cod_produto' => $produto['codigo'],
                'descricao' => $produto['descricao'],
                'ncm' => $produto['ncm'],
            ]);
        }
    }

    private function showSuccessNotification(): void
    {
        Notification::make()
            ->title('Sucesso')
            ->body('Produtos genéricos salvos com sucesso!')
            ->success()
            ->send();
    }

    private function showErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Erro')
            ->body('Erro ao salvar produtos genéricos: '.$message)
            ->danger()
            ->send();
    }

    public function getListeners(): array
    {
        return [
            'refreshComponent' => '$refresh',
        ];
    }
};
