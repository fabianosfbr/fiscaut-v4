<?php

namespace App\Filament\Actions;

use App\Services\Sefaz\NfsePdfGenerator;
use App\Services\Sefaz\SefazNfseDownloadService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

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
                if (isset($record->xml)) {
                    $generator = (new NfsePdfGenerator())
                    ->parseXml($record->xml);
                    
                    $pdfContent = $generator->generate()->Output('', 'S');
              
                    return response()->streamDownload(
                        function () use ($pdfContent) {
                            echo $pdfContent;
                        },
                        'nfse-' . $record->chave . '.pdf',
                        [
                            'Content-Type' => 'application/pdf',
                        ]
                    );
                }

                if (isset($record->link_download_pdf)) {
                    return redirect()->to($record->link_download_pdf);
                }

                Notification::make()
                    ->title('PDF da NFS-e não encontrado')
                    ->danger()
                    ->send();
            });
    }
}
