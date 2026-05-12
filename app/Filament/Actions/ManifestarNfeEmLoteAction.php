<?php

namespace App\Filament\Actions;

use App\Enums\StatusManifestoNfeEnum;
use App\Services\Sefaz\SefazNfeDownloadService;
use Exception;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class ManifestarNfeEmLoteAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('manifestar-nfe-em-lote')
            ->label('Manifestar NFe')
            ->requiresConfirmation()
            ->icon('heroicon-o-book-open')
            ->modalHeading('Manifestar Notas Fiscais')
            ->modalWidth('lg')
            ->modalDescription(new HtmlString('Insira os dados para manifestar as notas fiscais em lote.<br>Todas as notas fiscais selecionadas serão manifestadas com o mesmo status e justificativa.'))
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
                        return $get('status_manifestacao') != '210240';
                    }),
            ])
            ->action(function (Collection $records, array $data) {
                $issuer = currentIssuer();
                $service = new SefazNfeDownloadService($issuer);
                $sucesso = 0;
                $falha = 0;

                foreach ($records as $record) {
                    if (empty($record->xml)) {
                        $falha++;

                        continue;
                    }

                    $justificativa = $data['justificativa'] ?? '';

                    try {
                        $manifestado = $service->sefazManifesta($record->chave, $data['status_manifestacao'], $justificativa);

                        if ($manifestado) {
                            $record->update([
                                'data_manifesto' => date('Y-m-d H:i:s'),
                                'status_manifestacao' => StatusManifestoNfeEnum::from($data['status_manifestacao']),
                                'data_entrada' => isset($data['data_entrada']) ? str_replace('T', ' ', $data['data_entrada']) : $record->data_entrada,
                            ]);
                            $sucesso++;
                        } else {
                            $falha++;
                        }
                    } catch (Exception $e) {
                        $falha++;
                    }
                }

                $mensagem = "Manifestação concluída: {$sucesso} sucesso(s), {$falha} falha(s).";

                Notification::make()
                    ->title('Resultado da Manifestação')
                    ->body($mensagem)
                    ->success()
                    ->send();
            });
    }
}
