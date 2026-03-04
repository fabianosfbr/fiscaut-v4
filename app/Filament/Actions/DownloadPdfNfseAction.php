<?php

namespace App\Filament\Actions;

use App\Services\Sefaz\SefazNfseDownloadService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use NFePHP\DA\CTe\Dacte;

class DownloadPdfNfseAction
{
    public static function make(): Action
    {
        return Action::make('download-pdf-nfse')
            ->label('Download PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->requiresConfirmation()
            ->modalHeading('Download PDF')
            ->modalWidth('lg')
            ->modalDescription('Confirme o download do PDF.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, download')
            ->visible(fn(Model $record): bool => ! empty($record->xml))
            ->action(function ($record) {
                if (isset($record->chave_acesso)) {
                    $service = new SefazNfseDownloadService(currentIssuer());

                    $pdf = $service->getDanfse($record->chave_acesso);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf;
                    }, 'nfse-' . $record->chave_acesso . '.pdf');
                }

                Notification::make()
                    ->title('PDF da NFS-e não encontrado')
                    ->danger()
                    ->send();
            });
    }
}
