<?php

namespace App\Filament\Actions;

use App\Jobs\BulkAction\DownloadXmlNfseEmLoteActionJob;
use App\Jobs\BulkAction\DownloadXmlPdfNfceEmLoteActionJob;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DownloadXmlNfseEmLoteAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('download-xml-nfse-em-lote')
            ->label('Download XMLs')
            ->icon('heroicon-o-document-arrow-down')
            ->requiresConfirmation()
            ->modalHeading('Download XMLs')
            ->modalWidth('lg')
            ->modalDescription(function () {
                return new HtmlString('<p>Dica: Faça o download apenas dos arquivos que esteja precisando no momento. </p><p>O download de muitos arquivos dificulta na manipulação dos mesmos e pode deixar a internet lenta.</p>');
            })
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, download')
            ->action(function (Collection $records, array $data) {

                // Filtra apenas os registros que têm conteúdo XML
                $recordsWithXml = $records->filter(fn($record) => ! empty($record->xml));

                if ($recordsWithXml->isEmpty()) {

                    Notification::make()
                        ->title('Nenhum XML disponível para download')
                        ->danger()
                        ->send();

                    throw new Halt;
                }

                DownloadXmlNfseEmLoteActionJob::dispatch(
                    $records,
                    Auth::user()->id
                );

                Notification::make()
                    ->title('Exportação iniciada')
                    ->body('A exportação foi iniciada e as linhas selecionadas serão processadas em segundo plano')
                    ->success()
                    ->duration(2000)
                    ->send();
            });
    }
}
