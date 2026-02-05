<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EditTenant extends Page
{
    protected static ?string $title = 'Organização Principal';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.clusters.settings.pages.edit-tenant';

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
                Section::make('Informações da empresa')
                    ->description('Editar informações da empresa do perfil')
                    ->schema([
                        TextInput::make('name')
                            ->label('Razão Social')
                            ->required()
                            ->columnSpan('full')
                            ->autofocus(),
                        TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->required()
                            ->mask('99.999.999/9999-99')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('responsavel_tecnico')
                            ->label('Responsável Técnico')
                            ->columnSpan(1),
                        TextInput::make('crc_responsavel_tecnico')
                            ->label('CRC Responsável Técnico')
                            ->columnSpan(1),
                        TextInput::make('cpf_responsavel_tecnico')
                            ->label('CPF Responsável Técnico')
                            ->mask('999.999.999-99')
                            ->columnSpan(1),
                        TextInput::make('cep')
                            ->label('CEP')
                            ->extraAlpineAttributes(['x-mask' => '99999-999'])
                            ->columnSpan(1),
                        TextInput::make('endereco')
                            ->label('Endereço')
                            ->columnSpan(1),
                        TextInput::make('bairro')
                            ->label('Bairro')
                            ->columnSpan(1),
                        TextInput::make('cidade')
                            ->label('Cidade')
                            ->columnSpan(1),
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
