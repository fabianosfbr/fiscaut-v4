<?php

namespace App\Filament\Clusters\Settings\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconPosition;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Clusters\Settings\SettingsCluster;
use App\Filament\Infolists\Components\GenerateApiKeyEntry;

class Profile extends Page
{

    protected static ?string $title = 'Meu Perfil';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.clusters.settings.pages.profile';

    protected static ?string $cluster = SettingsCluster::class;

    public function personalInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getUser())
            ->schema([
                Section::make('Informações pessoais')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nome')
                            ->columnSpan(1),
                        TextEntry::make('email')
                            ->label('email')
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public function tenantInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getUser()->tenant()->first())
            ->schema([
                Section::make('Minha Empresa')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Razão Social')
                            ->columnSpan(1),
                        TextEntry::make('cnpj')
                            ->label('CNPJ')
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public function apiInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getUser())
            ->schema([
                Section::make('API')

                    ->schema([
                        TextEntry::make('keep_token')
                            ->label('API key')
                            ->icon('heroicon-m-key')
                            ->iconColor('warning')
                            ->fontFamily('mono')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->copyableState(fn ($state) => $state)
                            ->formatStateUsing(fn ($state) => $state ? '***********************************' . substr($state, -4) : '-')
                            ->columnSpan(1),
                        GenerateApiKeyEntry::make('generate_api_token')
                            ->label('Token')
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public function generateApiToken()
    {

        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('api_token')->plainTextToken;
        $user->keep_token = $token;
        $user->update();

        Notification::make()
            ->title('Dados atualizados com sucesso!')
            ->body('Seu token de acesso foi gerado/atualizado com sucesso.')
            ->color('success')
            ->duration(3000)
            ->send();
    }

    public function getUser()
    {
        return Auth::user();
    }
}
