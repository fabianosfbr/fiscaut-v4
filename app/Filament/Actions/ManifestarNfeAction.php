<?php

namespace App\Filament\Actions;

use App\Enums\StatusManifestoNfeEnum;
use App\Services\Sefaz\SefazNfeDownloadService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ManifestarNfeAction
{
    public static function make(): Action
    {
        return Action::make('manifestar-nfe')
            ->label('Manifestar')
            ->icon('heroicon-o-book-open')
            ->modalWidth('lg')
            ->modalHeading('Manifestar')
            ->modalDescription('Insira os dados para manifestar a nota fiscal eletrônica.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, manifestar')
            ->schema([
                DatePicker::make('data_entrada')
                    ->label('Data Entrada')
                    ->required()
                    ->format('Y-m-d')
                    ->weekStartsOnSunday()
                    ->default(now())
                    ->displayFormat('d/m/Y'),
                Select::make('status_manifestacao')
                    ->label('Status da Manifestação')
                    ->required()
                    ->options([
                        '210200' => 'Confirmação da Operação',
                        '210220' => 'Desconhecimento da Operação',
                        '210240' => 'Operação não Realizada',
                    ])
                    ->live()
                    ->afterStateUpdated(
                        fn ($state, callable $set) => $state ? $set('justificativa', null) : $set('justificativa', 'hidden')
                    ),
                Textarea::make('justificativa')
                    ->label('Justificativa')
                    ->required()
                    ->hidden(function ($get) {
                        return $get('status_manifestacao') != 210240;
                    }),
            ])
            ->action(function (array $data, Model $record) {
                if (empty($record->xml)) {
                    return;
                }
                $justificativa = array_key_exists('justificativa', $data) ? $data['justificativa'] : '';

                $issuer = currentIssuer();
                $service = new SefazNfeDownloadService($issuer);

                $manifestado = $service->sefazManifesta($record->chave, $data['status_manifestacao'], $justificativa);

                if ($manifestado) {
                    $record->update([
                        'data_manifesto' => date('Y-m-d H:i:s'),
                        'status_manifestacao' => StatusManifestoNfeEnum::from($data['status_manifestacao']),
                        'data_entrada' => isset($data['data_entrada']) ? str_replace('T', ' ', $data['data_entrada']) : $record->data_entrada,
                    ]);

                    Notification::make()
                        ->title('Nota manifestada com sucesso!')
                        ->success()
                        ->send();
                }
            });
    }
}
