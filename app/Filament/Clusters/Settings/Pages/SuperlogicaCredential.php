<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Exceptions\SuperlogicaConnectionException;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Services\SuperlogicaConnectionService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SuperlogicaCredential extends Page
{
    protected static ?string $title = 'Credenciais de Acesso à Superlógica';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.clusters.settings.pages.superlogica-credential';

    protected static ?string $cluster = SettingsCluster::class;

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = Auth::user()->tenant()->first();
        $this->form->fill($tenant->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações de integração com a Superlógica')
                    ->description('Forneça a URL base e os tokens de autenticação para validar a conexão.')
                    ->schema([
                        TextInput::make('superlogica_base_url')
                            ->label('URL Base da Superlógica')
                            ->url()
                            ->default('https://api.superlogica.net/v2/condor')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('superlogica_app_token')
                            ->label('App Token')
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        TextInput::make('superlogica_access_token')
                            ->label('Access Token')
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $tenant = Auth::user()->tenant()->first();
        $tenant->update($this->form->getState());

        Notification::make()
            ->title('Dados atualizados com sucesso')
            ->success()
            ->send();
    }

    public function testConnection(SuperlogicaConnectionService $service): void
    {
        $user = Auth::user();
        $issuer = $user?->currentIssuer;

        if (! $issuer) {
            Notification::make()
                ->title('Não foi possível validar')
                ->body('Selecione um emissor ativo para testar a conexão.')
                ->warning()
                ->send();

            return;
        }

        try {
            $service->validateConnection($issuer);

            Notification::make()
                ->title('Conexão validada com sucesso')
                ->success()
                ->send();
        } catch (SuperlogicaConnectionException $exception) {
            Notification::make()
                ->title('Falha ao validar conexão')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }
}
