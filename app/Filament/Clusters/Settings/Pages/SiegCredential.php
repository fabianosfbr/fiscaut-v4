<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SiegCredential extends Page
{
    protected static ?string $title = 'Credenciais de Acesso ao Sieg';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.clusters.settings.pages.sieg-credential';

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
                Section::make('Informações de integração com o Sieg')
                    ->description('Forneça as credenciais de integração de API do Sieg')
                    ->schema([
                        TextInput::make('sieg_key')
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
