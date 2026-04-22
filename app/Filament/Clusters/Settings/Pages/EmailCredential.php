<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EmailCredential extends Page
{
    protected static ?string $title = 'Configurações de E-mail';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.clusters.settings.pages.email-credential';

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
                Section::make('Configurações do servidor SMTP')
                    ->description('Forneça as credenciais do servidor de e-mail para envio de notificações.')
                    ->schema([
                        TextInput::make('smtp_host')
                            ->label('Servidor SMTP (Host)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('smtp_port')
                            ->label('Porta')
                            ->required()
                            ->numeric()
                            ->minLength(2)
                            ->maxLength(5),
                        TextInput::make('smtp_username')
                            ->label('Usuário (E-mail)')
                            ->required()
                            ->email()
                            ->maxLength(255),
                        TextInput::make('smtp_password')
                            ->label('Senha')
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        Select::make('smtp_encryption')
                            ->label('Criptografia')
                            ->required()
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ]),
                    ])->columns(2),
                Section::make('Informações do Remetente')
                    ->description('Configure o e-mail que aparecerá como remetente nas notificações.')
                    ->schema([
                        TextInput::make('smtp_from_email')
                            ->label('E-mail do Remetente')
                            ->required()
                            ->email()
                            ->maxLength(255),
                        TextInput::make('smtp_from_name')
                            ->label('Nome do Remetente')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
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