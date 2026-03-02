<?php

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\Cfop;
use App\Models\EntradaCfopEquivalente;
use App\Models\GrupoEntradaCfopEquivalente;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

    const TIPO = 'nfe-entrada-terceiro';

    public ?array $data = [];

    public bool $isLoading = false;

    public bool $hasChanges = false;

    public function mount(): void
    {
        $this->loadCurrentSettings();
    }

    protected function loadCurrentSettings(): void
    {
        $currentIssuer = currentIssuer();

        if (! $currentIssuer) {
            $this->form->fill([]);

            return;
        }

        $grupos = GrupoEntradaCfopEquivalente::getAllCached($currentIssuer->id, $currentIssuer->tenant_id, self::TIPO);

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
                'cfops' => $this->transformCfops($grupo->cfopsEquivalentes),
            ];
        })->toArray();
    }

    private function extractTagId($tags): ?array
    {
        if (empty($tags) || ! is_array($tags)) {
            return [];
        }

        return $tags;
    }

    private function transformCfops($cfops): array
    {
        if ($cfops->isEmpty()) {
            return [];
        }

        return $cfops->map(function ($cfop) {
            return [
                'cfop_entrada' => $cfop->cfop_entrada,
                'cfops_saida' => $cfop->valores ?? [],
                'aplicar_uf_diferente' => $cfop->uf_diferente ?? false,
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
                            ->multiple(true)
                            ->options(CategoryTag::getAllEnabled(currentIssuer()->id))
                            ->columnSpan(2),

                        Repeater::make('cfops')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('cfop_entrada')
                                    ->label('CFOP')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->options(fn () => Cfop::all()->pluck('descricao', 'codigo'))
                                    ->columnSpan(2),

                                TagsInput::make('cfops_saida')
                                    ->label('CFOPs Saída')
                                    ->placeholder('Digite os CFOPs e pressione Enter')
                                    ->separator(',')
                                    ->splitKeys(['Enter', ',', ' '])
                                    ->helperText('Digite um ou mais CFOPs de saída')
                                    ->columnSpan(2),

                                Checkbox::make('aplicar_uf_diferente')
                                    ->label('Aplicar UF diferente')
                                    ->helperText('Aplicar quando UF do emitente for diferente da UF do destinatário')
                                    ->inline()
                                    ->columnSpan(1),
                            ])
                            ->columns(5)
                            ->itemLabel(fn (array $state): ?string => isset($state['cfop_entrada']) ? "CFOP: {$state['cfop_entrada']}" : null)
                            ->addActionLabel('Adicionar CFOP')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => ! empty($state['tag_id']) ? 'Etiquetas: '.count($state['tag_id']) : 'Sem etiquetas')
                    ->addActionLabel('Adicionar Etiqueta')
                    ->reorderable()
                    ->collapsible()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'data.')) {
            $this->hasChanges = true;
        }
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
            GrupoEntradaCfopEquivalente::invalidateCache($currentIssuer->id, $currentIssuer->tenant_id, self::TIPO);

            $this->hasChanges = false;
            $this->showSuccessNotification();
            $this->refreshFormData();
        } catch (\Exception $e) {
            Log::error('Erro ao salvar CFOPs equivalentes: '.$e->getMessage());
            $this->showErrorNotification($e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    private function clearExistingGroups($currentIssuer): void
    {
        $grupos = GrupoEntradaCfopEquivalente::where('issuer_id', $currentIssuer->id)
            ->where('tenant_id', $currentIssuer->tenant_id)
            ->get();

        foreach ($grupos as $grupo) {
            $this->clearExistingCfops($grupo->id);
            $grupo->delete();
        }
    }

    private function clearExistingCfops($grupoId): void
    {
        EntradaCfopEquivalente::where('grupo_id', $grupoId)
            ->where('tipo', self::TIPO)
            ->delete();
    }

    private function createNewGroups(array $formData, $currentIssuer): void
    {
        if (empty($formData['itens'])) {
            return;
        }

        foreach ($formData['itens'] as $item) {
            $grupo = $this->createGroup($item, $currentIssuer);
            $this->createCfopsForGroup($item, $grupo, $currentIssuer);
        }
    }

    private function createGroup(array $item, $currentIssuer): GrupoEntradaCfopEquivalente
    {
        return GrupoEntradaCfopEquivalente::create([
            'tenant_id' => $currentIssuer->tenant_id,
            'issuer_id' => $currentIssuer->id,
            'tags' => $item['tag_id'] ?? [],
        ]);
    }

    private function createCfopsForGroup(array $item, GrupoEntradaCfopEquivalente $grupo, $currentIssuer): void
    {
        if (empty($item['cfops'])) {
            return;
        }

        foreach ($item['cfops'] as $cfop) {
            EntradaCfopEquivalente::create([
                'tenant_id' => $currentIssuer->tenant_id,
                'grupo_id' => $grupo->id,
                'cfop_entrada' => $cfop['cfop_entrada'],
                'valores' => $cfop['cfops_saida'] ?? [],
                'tipo' => self::TIPO,
                'uf_diferente' => $cfop['aplicar_uf_diferente'] ?? false,
            ]);
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
        $this->dispatch('$refresh');
    }

    private function showSuccessNotification(): void
    {
        Notification::make()
            ->title('Sucesso')
            ->body('CFOPs equivalentes salvos com sucesso!')
            ->success()
            ->send();
    }

    private function showErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Erro')
            ->body('Erro ao salvar CFOPs equivalentes: '.$message)
            ->danger()
            ->send();
    }
};
