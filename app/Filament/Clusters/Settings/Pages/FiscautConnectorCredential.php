<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FiscautConnectorCredential extends Page
{
    protected static ?string $title = 'Credenciais de Acesso ao FC';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.clusters.settings.pages.fiscaut-connector-credential';

    protected static ?string $cluster = SettingsCluster::class;

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = Auth::user()->tenant()->first();

        $data = $tenant->attributesToArray();

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações de integração com o Fiscaut Connector')
                    ->description('Forneça as credenciais de integração de API do Fiscaut Connector')
                    ->schema([
                        TextInput::make('fiscaut_connector_url')
                            ->label('URL do Fiscaut Connector')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('fiscaut_connector_token')
                            ->label('Chave de acesso')
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
        $data = $this->form->getState();

        $tenant = Auth::user()->tenant()->first();

        $tenant->update($data);

        Notification::make()
            ->title('Dados atualizados com sucesso')
            ->success()
            ->send();
    }
}
