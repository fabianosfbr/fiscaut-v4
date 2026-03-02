<?php

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\Acumulador;
use App\Models\CategoryTag;
use App\Models\EntradaAcumuladorEquivalente;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    const TIPO = 'nfe-entrada-propria';

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

        $entradas = EntradaAcumuladorEquivalente::getAllCached($currentIssuer->id, $currentIssuer->tenant_id, self::TIPO);

        $formData = $this->transformDatabaseToFormData($entradas);

        $this->form->fill([
            'itens' => $formData,
        ]);
    }

    private function transformDatabaseToFormData($entradas): array
    {
        if ($entradas->isEmpty()) {
            return [];
        }

        return $entradas->map(function ($entrada) {
            $valores = [];
            if (is_array($entrada->valores)) {
                foreach ($entrada->valores as $valor) {
                    $valores[] = (string) $valor;
                }
            }

            $cfops = [];
            if (is_array($entrada->cfops)) {
                foreach ($entrada->cfops as $cfop) {
                    $cfops[] = (string) $cfop;
                }
            }

            return [
                'etiqueta_entrada' => $entrada->etiqueta_entrada ?? null,
                'valores' => $valores,
                'cfops' => $cfops,
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
                        SelectTagGrouped::make('etiqueta_entrada')
                            ->label('Acumulador')
                            ->columnSpan(5)
                            ->multiple(false)
                            ->options(function () {
                                $issuer = currentIssuer();
                                if (! $issuer) {
                                    return \Illuminate\Database\Eloquent\Collection::make();
                                }

                                $acumuladores = Acumulador::where('issuer_id', $issuer->id)->get();

                                $category = (object) [
                                    'id' => 'acumuladores',
                                    'name' => 'Acumuladores',
                                    'color' => '#3b82f6',
                                    'tags' => $acumuladores->map(function (Acumulador $acumulador) {
                                        return (object) [
                                            'id' => (string) $acumulador->codi_acu,
                                            'code' => $acumulador->codi_acu,
                                            'name' => $acumulador->nome_acu,
                                            'color' => '#3b82f6',
                                        ];
                                    })->values(),
                                ];

                                return \Illuminate\Database\Eloquent\Collection::make([$category]);
                            }),

                        SelectTagGrouped::make('valores')
                            ->label('Etiqueta')
                            ->multiple(true)
                            ->options(CategoryTag::getAllEnabled(currentIssuer()->id))
                            ->columnSpan(5),

                        TagsInput::make('cfops')
                            ->label('CFOPs')
                            ->hint('Já convertido')
                            ->placeholder('Digite o CFOP e tecle ENTER para adicionar')
                            ->columnSpan(5),

                    ])
                    ->columns(5)
                    ->addActionLabel('Adicionar Acumulador')
                    ->itemLabel(fn (array $state): ?string => ! empty($state['etiqueta_entrada']) ? 'Acumulador: '.$state['etiqueta_entrada'] : 'Sem acumulador')
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

            EntradaAcumuladorEquivalente::where('tipo', self::TIPO)
                ->where('issuer_id', $currentIssuer->id)
                ->where('tenant_id', $currentIssuer->tenant_id)
                ->delete();

            foreach ($formData['itens'] as $item) {
                EntradaAcumuladorEquivalente::create([
                    'tenant_id' => $currentIssuer->tenant_id,
                    'issuer_id' => $currentIssuer->id,
                    'etiqueta_entrada' => $item['etiqueta_entrada'],
                    'valores' => $item['valores'],
                    'cfops' => $item['cfops'],
                    'tipo' => self::TIPO,
                ]);
            }

            // Invalida o cache ANTES de recarregar os dados
            EntradaAcumuladorEquivalente::invalidateCache($currentIssuer->id, $currentIssuer->tenant_id, self::TIPO);

            $this->hasChanges = false;
            $this->showSuccessNotification();
            $this->refreshFormData();
        } catch (\Exception $e) {
            Log::error('Erro ao salvar Acumuladores NFe Notas Própria: '.$e->getMessage());
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
        $this->dispatch('$refresh');
    }

    private function showSuccessNotification(): void
    {
        Notification::make()
            ->title('Sucesso')
            ->body('Acumuladores NFe Notas Própria salvos com sucesso!')
            ->success()
            ->send();
    }

    private function showErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Erro')
            ->body('Erro ao salvar Acumuladores NFe Notas Própria: '.$message)
            ->danger()
            ->send();
    }
};
