<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use NFePHP\DA\NFe\Danfe;

class DownloadPdfNfeAction
{
    public static function make(): Action
    {
        return Action::make('download-pdf')
            ->label('Download PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->requiresConfirmation()
            ->modalHeading('Download PDF')
            ->modalWidth('lg')
            ->modalDescription('Confirme o download do PDF.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, download')
            ->action(function ($record) {

                if (empty($record->xml)) {
                    return;
                }

                $xml_content = gzuncompress($record->xml);

                $filename = "{$record->chave}.pdf";

                $danfe = new Danfe($xml_content);
                $danfe->creditsIntegratorFooter(config('admin.footer_credits_danfe'), false);
                $pdf = $danfe->render();

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf;
                }, $filename);
            });
    }
}
