<?php

namespace App\Filament\Condominio\Pages;

use App\Models\GeneralSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use UnitEnum;

class TemplateCobranca extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.condominio.pages.template-cobranca';

    protected static string|UnitEnum|null $navigationGroup = 'Cobranças';

    protected static ?string $title = 'Template de Cobrança';

    protected static string $settingName = 'template_cobranca';

    protected static ?string $slug = 'template-cobranca';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public bool $isLoading = false;

    public bool $hasChanges = false;

    protected $listeners = [
        'currentIssuerChanged' => 'handlecurrentIssuerChanged',
    ];

    protected function getDefaultTemplate(): string
    {
        return <<<HTML
<p><strong>Unidade {{numero_unidade}} Bloco {{bloco_quadra}}</strong></p>        
<p>Prezado(a) {{nome_morador}},</p>
<p>Esperamos que esteja bem.</p>
<p>Identificamos em nosso sistema a existência de pendência(s) referente(s) às taxas condominiais da unidade {{numero_unidade}}.</p>
<p>Detalhes da(s) pendência(s):</p>
<p>{{ titulos_aberto }}</p>
<p>Pedimos a gentileza de verificar e, se possível, regularizar o(s) débito(s) o quanto antes, a fim de evitar a incidência de encargos adicionais, como multa e juros, conforme previsto na convenção do condomínio.</p>
<p>Caso o pagamento já tenha sido realizado, por favor, desconsidere esta mensagem ou nos envie o comprovante para atualização do sistema.</p>
<p>Se precisar de qualquer esclarecimento ou desejar negociar o débito, estamos à disposição.</p>
<p>Atenciosamente,</p>
HTML;
    }

    public function mount(): void
    {
        $this->loadCurrentSettings();
    }

    protected function loadCurrentSettings(): void
    {
        $currentIssuer = currentIssuer();

        if (!$currentIssuer) {
            $this->form->fill([
                'mensagem' => $this->getDefaultTemplate(),
            ]);

            return;
        }

        $settings = GeneralSetting::getAll(
            self::$settingName,
            $currentIssuer->id,
            $currentIssuer->tenant_id
        );

        if (empty($settings['mensagem'])) {
            $settings['mensagem'] = $this->getDefaultTemplate();
        }

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template de Notificação de Cobrança')
                    ->description('Personalize a mensagem que será enviada aos moradores inadimplentes. Use as variáveis entre chaves para dynamicizar o conteúdo.')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        RichEditor::make('mensagem')
                            ->label('Mensagem')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->live()
                            ->afterStateUpdated(fn() => $this->hasChanges = true),

                        Group::make([
                            Actions::make([
                                Action::make('resetDefault')
                                    ->label('Restaurar Padrão')
                                    ->icon('heroicon-m-arrow-path')
                                    ->color('gray')
                                    ->requiresConfirmation()
                                    ->action(function () {
                                        $this->form->fill([
                                            'mensagem' => $this->getDefaultTemplate(),
                                        ]);
                                        $this->hasChanges = true;
                                    }),
                            ]),
                        ]),
                    ]),
                Section::make('Variáveis Disponíveis')
                    ->description('Estas variáveis serão substituídas automaticamente na mensagem')
                    ->icon('heroicon-m-code-bracket')
                    ->schema([
                        TextEntry::make('variaveis')
                            ->hiddenLabel()
                            ->state(function (): ?HtmlString {

                                return new HtmlString("
                                <div>
                                    <p><strong>Para substituir o número da unidade:</strong> {{numero_unidade}}</p>
                                    <p><strong>Para substituir o nome do bloco/quadra:</strong> {{bloco_quadra}}</p>
                                    <p><strong>Para substituir o nome do morador:</strong> {{nome_morador}}
                                    <p><strong>Para substituir os títulos em aberto:</strong> {{titulos_aberto}}
                                </div>
                                
                                ");
                            }),
                       
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $currentIssuer = currentIssuer();

        if (!$currentIssuer) {
            Notification::make()
                ->title('Empresa não selecionada')
                ->body('Selecione uma empresa para salvar o template.')
                ->warning()
                ->send();

            return;
        }

        $this->isLoading = true;

        try {
            $formData = $this->form->getState();

            GeneralSetting::setValue(
                self::$settingName,
                $formData,
                $currentIssuer->id,
                Auth::user()->tenant_id
            );

            $this->hasChanges = false;

            Notification::make()
                ->title('Template salvo')
                ->body('O template de cobrança foi salvo com sucesso.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao salvar')
                ->body(new HtmlString('Ocorreu um erro ao salvar o template.<br>' . $e->getMessage()))
                ->danger()
                ->send();
        } finally {
            $this->isLoading = false;
        }
    }
}
