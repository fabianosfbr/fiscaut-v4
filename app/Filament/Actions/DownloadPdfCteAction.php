<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use NFePHP\DA\CTe\Dacte;

class DownloadPdfCteAction
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
            ->visible(fn (Model $record): bool => ! empty($record->xml))
            ->action(function ($record) {
                $xml_content = gzuncompress($record->xml);

                $filename = "{$record->chave}.pdf";

                $danfe = new Dacte($xml_content);
                $danfe->creditsIntegratorFooter(config('admin.footer_credits_danfe'), false);
                $pdf = $danfe->render();

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf;
                }, $filename);
            });
    }
}
