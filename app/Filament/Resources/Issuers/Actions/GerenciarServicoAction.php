<?php

namespace App\Filament\Resources\Issuers\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class GerenciarServicoAction
{
    /**
     * Configuração centralizada dos serviços disponíveis
     */
    public static function getServicosConfig(): array
    {
        return [
            'nfe_servico' => [
                'label' => 'NFe - Nota Fiscal Eletrônica',
                'short_label' => 'NFe',
                'icon' => '📄',
                'color' => '#059669',
                'description' => 'Emissão e gerenciamento de Notas Fiscais Eletrônicas',
            ],
            'cte_servico' => [
                'label' => 'CTe - Conhecimento de Transporte Eletrônico',
                'short_label' => 'CTe',
                'icon' => '🚛',
                'color' => '#0ea5e9',
                'description' => 'Emissão e gerenciamento de Conhecimentos de Transporte',
            ],
            'sync_sieg' => [
                'label' => 'SIEG - Sincronização com serviços SIEG',
                'short_label' => 'SIEG',
                'icon' => '🔄',
                'color' => '#8b5cf6',
                'description' => 'Sincronização com serviços SIEG',
            ],
            'sync_unecont' => [
                'label' => 'NFSe - Nota Fiscal de Serviços Eletrônica',
                'short_label' => 'NFSe',
                'icon' => '🏢',
                'color' => '#f59e0b',
                'description' => 'Sincronização para recuperação de NFSe Tomadas com as Prefeituras',
            ],
        ];
    }

    /**
     * Retorna as opções formatadas para CheckboxList
     */
    public static function getServicosOptions(): array
    {
        return collect(self::getServicosConfig())->mapWithKeys(function ($config, $key) {
            return [$key => $config['label']];
        })->toArray();
    }

    /**
     * Retorna as descrições para CheckboxList
     */
    public static function getServicosDescriptions(): array
    {
        return collect(self::getServicosConfig())->mapWithKeys(function ($config, $key) {
            return [$key => $config['description']];
        })->toArray();
    }

    public static function make(): Action
    {
        return Action::make('gerenciar_servicos')
            ->label('Gerenciar Serviços')
            ->color('primary')
            ->icon('heroicon-o-cog-6-tooth')
            ->schema([
                CheckboxList::make('servicos')
                    ->label('Selecione os servi\u00e7os a serem habilitados')
                    ->options(self::getServicosOptions())
                    ->descriptions(self::getServicosDescriptions())
                    ->columns(1)
                    ->gridDirection('row'),

                Checkbox::make('ignorar_sync_superlogica')
                    ->label('Ignorar sincronização Superlógica')
                    ->live(),

                TextInput::make('superlogica_condominio_id')
                    ->label('ID Superlógica')
                    ->numeric()
                    ->disabled(fn (Get $get): bool => ! $get('ignorar_sync_superlogica'))
                    ->required(fn (Get $get): bool => ! $get('ignorar_sync_superlogica')),
            ])
            ->fillForm(function (Model $record): array {
                return [
                    'servicos' => collect(self::getServicosConfig())
                        ->filter(fn ($config, $key) => $record->{$key})
                        ->keys()
                        ->toArray(),
                    'superlogica_condominio_id' => $record->superlogica_condominio_id,
                    'ignorar_sync_superlogica' => $record->ignorar_sync_superlogica,
                ];
            })
            ->action(function (Model $record, array $data): void {
                // Preparar os dados para atualiza\u00e7\u00e3o usando a configura\u00e7\u00e3o centralizada
                $updateData = [];
                foreach (array_keys(self::getServicosConfig()) as $servicoKey) {
                    $updateData[$servicoKey] = in_array($servicoKey, $data['servicos']);
                }

                // Atualizar os dados da Superl\u00f3gica
                $updateData['superlogica_condominio_id'] = $data['superlogica_condominio_id'];
                $updateData['ignorar_sync_superlogica'] = $data['ignorar_sync_superlogica'];

                // Atualizar os servi\u00e7os
                $record->update($updateData);

                Notification::make()
                    ->title('Serviços Atualizados!')
                    ->body("Serviços atualizados para {$record->razao_social}")
                    ->success()
                    ->send();
            })
            ->modalHeading('Gerenciar Serviços da Empresa')
            ->modalDescription(function (Model $record) {
                return "Configure quais serviços estarão ativos para {$record->razao_social}";
            })
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel('Salvar Configuração')
            ->modalCancelActionLabel('Cancelar')
            ->after(function ($livewire) {
                $livewire->redirect(request()->header('Referer'));
            });
    }
}
