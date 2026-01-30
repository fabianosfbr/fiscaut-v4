<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

class DownloadXmlAction
{
    public static function make(): Action
    {
        return Action::make('download')
            ->label('Download XML')
            ->icon('heroicon-o-document-arrow-down')
            ->requiresConfirmation()
            ->modalHeading('Download XML')
            ->modalWidth('lg')
            ->modalDescription('Confirme o download do XML.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, download')
            ->action(function ($record) {
                if (empty($record->xml)) {
                    return;
                }
                $xml_content = gzuncompress($record->xml);
                $filename = "{$record->chave}.xml";

                return response()->streamDownload(function () use ($xml_content) {
                    echo $xml_content;
                }, $filename);
            });
    }
}
