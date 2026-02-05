<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page
{
    protected static ?string $title = 'Alterar Senha';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.clusters.settings.pages.change-password';

    protected static ?string $cluster = SettingsCluster::class;

    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Alterar senha')
                    ->description('Alterar senha do usuário')
                    ->schema([
                        TextInput::make('password')
                            ->label('Senha')
                            ->required()
                            ->password()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation')
                            ->autofocus(),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmar senha')
                            ->password()
                            ->required()
                            ->visible(fn (Get $get): bool => filled($get('password')))
                            ->dehydrated(false),
                    ]),
                // ...
            ])
            ->model(Auth::user())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();
        $user->password = $data['password'];
        $user->save();

        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put([
                'password_hash_'.Filament::getAuthGuard() => $data['password'],
            ]);
        }

        $this->data['password'] = null;
        $this->data['passwordConfirmation'] = null;

        Notification::make()
            ->title('Senha alterada com sucesso')
            ->success()
            ->send();
    }
}
