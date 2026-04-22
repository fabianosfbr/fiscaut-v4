<?php

namespace App\Filament\Condominio\Pages;

use App\Models\GeneralSetting;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use UnitEnum;

class ConfiguracaoCobranca extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.condominio.pages.configuracao-cobranca';

    protected static string|UnitEnum|null $navigationGroup = 'Cobranças';

    protected static ?string $title = 'Configuração de Cobrança';

    protected static string $settingName = 'configuracoes_gerais';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public bool $isLoading = false;

    public bool $hasChanges = false;

    protected $listeners = [
        'currentIssuerChanged' => 'handlecurrentIssuerChanged',
    ];

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
                Section::make('Notificações de Cobrança')
                    ->description('Configure quando deseja notificar sobre cobranças')
                    ->icon('heroicon-m-bell-alert')
                    ->collapsible()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Checkbox::make('notificacao_cobranca_depois.enabled')
                                    ->label('Notificar depois do vencimento')
                                    ->live()
                                    ->columnSpan(1)
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),

                                TextInput::make('notificacao_cobranca_depois.dias')
                                    ->label('Dias depois do vencimento')
                                    ->visible(fn ($get) => $get('notificacao_cobranca_depois.enabled'))
                                    ->live()
                                    ->columnSpan(1)
                                    ->afterStateUpdated(fn () => $this->hasChanges = true),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $currentIssuer = currentIssuer();

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
}
