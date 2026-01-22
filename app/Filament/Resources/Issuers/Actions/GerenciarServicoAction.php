<?php

namespace App\Filament\Resources\Issuers\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
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
                    ->label('Selecione os serviços a serem habilitados')
                    ->options(self::getServicosOptions())
                    ->descriptions(self::getServicosDescriptions())
                    ->columns(1)
                    ->gridDirection('row'),
            ])
            ->fillForm(function (Model $record): array {
                return [
                    'servicos' => collect(self::getServicosConfig())
                        ->filter(fn($config, $key) => $record->{$key})
                        ->keys()
                        ->toArray(),
                ];
            })
            ->action(function (Model $record, array $data): void {
                // Preparar os dados para atualização usando a configuração centralizada
                $updateData = [];
                foreach (array_keys(self::getServicosConfig()) as $servicoKey) {
                    $updateData[$servicoKey] = in_array($servicoKey, $data['servicos']);
                }

                // Atualizar os serviços
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
