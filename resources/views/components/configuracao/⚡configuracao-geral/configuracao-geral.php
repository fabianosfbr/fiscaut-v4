<?php

use App\Enums\ConfiguracoesGeraisEnum;
use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\GeneralSetting;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Livewire\Component;

new class extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public bool $isLoading = false;

    public bool $hasChanges = false;

    protected static string $settingName = 'configuracoes_gerais';

    protected $listeners = [
        'currentIssuerChanged' => 'handlecurrentIssuerChanged',
    ];

    public function mount(): void
    {
        $this->loadCurrentSettings();
    }

    protected function loadCurrentSettings(): void
    {
        $currentIssuer = Auth::user()->currentIssuer;

        if (! $currentIssuer) {
            $this->form->fill([]);

            return;
        }

        $settings = GeneralSetting::getAll(
            self::$settingName,
            $currentIssuer->id,
            $currentIssuer->tenant_id
        );

        // dd($settings);

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Configurações Gerais')
                    ->description('Configure o comportamento do sistema para esta empresa')
                    ->icon('heroicon-m-cog-6-tooth')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Checkbox::make(ConfiguracoesGeraisEnum::IsNfeClassificarNaEntrada->value)
                                    ->label(ConfiguracoesGeraisEnum::IsNfeClassificarNaEntrada->getLabel())
                                    ->helperText('Quando ativado, permite informar a data de entrada ao classificar uma NFe')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                                Checkbox::make(ConfiguracoesGeraisEnum::IsNfeManifestarAutomatica->value)
                                    ->label(ConfiguracoesGeraisEnum::IsNfeManifestarAutomatica->getLabel())
                                    ->helperText('Quando ativado, o sistema realizará a manifestação automática das notas')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                                Checkbox::make(ConfiguracoesGeraisEnum::IsNfeClassificarSomenteManifestacao->value)
                                    ->label(ConfiguracoesGeraisEnum::IsNfeClassificarSomenteManifestacao->getLabel())
                                    ->helperText('Quando ativado, a classificação da NFe só será realizada após a manifestação')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                                Checkbox::make(ConfiguracoesGeraisEnum::IsNfeMostrarCodigoEtiqueta->value)
                                    ->label(ConfiguracoesGeraisEnum::IsNfeMostrarCodigoEtiqueta->getLabel())
                                    ->helperText('Quando ativado, o sistema mostrará o código da etiqueta ao invés do nome abreviado')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                                Checkbox::make(ConfiguracoesGeraisEnum::IsNfeTomaCreditoIcms->value)
                                    ->label(ConfiguracoesGeraisEnum::IsNfeTomaCreditoIcms->getLabel())
                                    ->helperText('Quando ativado, o sistema considerará crédito de ICMS para notas com CFOP 1.401')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                                Checkbox::make(ConfiguracoesGeraisEnum::VerificarUfEmitenteDestinatario->value)
                                    ->label(ConfiguracoesGeraisEnum::VerificarUfEmitenteDestinatario->getLabel())
                                    ->helperText('Quando ativado, verifica a UF do emitente e destinatário para processar os CFOPs corretamente')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                                SelectTagGrouped::make('tagsCreditoIcms')
                                    ->label('Notas com as etiquetas abaixo serão consideradas como credito de ICMS')
                                    ->multiple(true)
                                    ->options(CategoryTag::getAllEnabled(Auth::user()->currentIssuer->id))
                                    ->required(function ($get) {

                                        return $get('isNfeTomaCreditoIcms');
                                    })
                                    ->disabled(function ($get) {
                                        return ! $get('isNfeTomaCreditoIcms');
                                    })
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->hasChanges = true)
                                    ->validationMessages([
                                        'required' => 'É obrigatório informar as etiquetas para credito de ICMS',
                                    ]),

                                Checkbox::make(ConfiguracoesGeraisEnum::IsClassificarCteVinculadoANfe->value)
                                    ->label(ConfiguracoesGeraisEnum::IsClassificarCteVinculadoANfe->getLabel())
                                    ->helperText('Quando ativado, o sistema classificará o CTE vinculado à NFe quando etiquetado')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Salva as configurações.
     */
    public function save(): void
    {
        $currentIssuer = Auth::user()->currentIssuer;

        if (! $currentIssuer) {
            Notification::make()
                ->title('Empresa não selecionada')
                ->body('Selecione uma empresa para salvar as configurações.')
                ->warning()
                ->send();

            return;
        }

        $this->isLoading = true;

        try {
            $formData = $this->form->getState();

            // Converte null para false nos checkboxes para consistência
            // Mas preserva arrays vazios para campos múltiplos
            $cleanData = array_map(function ($value) {
                // Se for array, manter como array (mesmo que vazio)
                if (is_array($value)) {
                    return $value;
                }

                // Para outros valores, converter null para false
                return $value ?? false;
            }, $formData);

            // Salvar configurações
            $setting = GeneralSetting::setValue(
                self::$settingName,
                $cleanData,
                $currentIssuer->id,
                Auth::user()->tenant_id
            );

            $this->hasChanges = false;

            Notification::make()
                ->title('Configurações salvas')
                ->body('As configurações foram salvas com sucesso.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao salvar')
                ->body(new HtmlString('Ocorreu um erro ao salvar as configurações.<br>'.$e->getMessage()))
                ->danger()
                ->send();
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Recarrega as configurações do banco de dados.
     */
    public function reload(): void
    {
        $this->loadCurrentSettings();
        $this->hasChanges = false;

        Notification::make()
            ->title('Configurações recarregadas')
            ->body('As configurações foram recarregadas do banco de dados.')
            ->info()
            ->send();
    }

    /**
     * Reseta todas as configurações para os valores padrão.
     */
    public function resetToDefaults(): void
    {
        $this->form->fill([
            ConfiguracoesGeraisEnum::IsNfeClassificarNaEntrada->value => false,
            ConfiguracoesGeraisEnum::IsNfeManifestarAutomatica->value => false,
            ConfiguracoesGeraisEnum::IsNfeClassificarSomenteManifestacao->value => false,
            ConfiguracoesGeraisEnum::IsNfeMostrarCodigoEtiqueta->value => false,
            ConfiguracoesGeraisEnum::IsNfeTomaCreditoIcms->value => false,
            ConfiguracoesGeraisEnum::VerificarUfEmitenteDestinatario->value => false,
            // 'tags_com_credito_icms' => [], // These seemed to be unused or commented out in the schema
            'tags_id' => [], // Adicionar campo tags_id com array vazio como padrão
        ]);

        $this->hasChanges = true;

        Notification::make()
            ->title('Configurações resetadas')
            ->body('Todas as configurações foram resetadas para os valores padrão.')
            ->warning()
            ->send();
    }

    /**
     * Verifica se existem mudanças não salvas.
     */
    public function hasUnsavedChanges(): bool
    {
        return $this->hasChanges;
    }
    //
};
