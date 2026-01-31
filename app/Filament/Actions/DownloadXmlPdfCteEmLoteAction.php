<?php

namespace App\Filament\Actions;

use App\Jobs\BulkAction\DownloadXmlPdfCteEmLoteActionJob;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DownloadXmlPdfCteEmLoteAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('download-xml-pdf-cte-em-lote')
            ->label('Download XMLs e PDFs')
            ->icon('heroicon-o-document-arrow-down')
            ->requiresConfirmation()
            ->modalHeading('Download XMLs e PDFs')
            ->modalWidth('lg')
            ->modalDescription(function () {
                return new HtmlString('<p>Dica: Faça o download apenas dos arquivos que esteja precisando no momento. </p><p>O download de muitos arquivos dificulta na manipulação dos mesmos e pode deixar a internet lenta.</p>');
            })
            ->schema([
                Grid::make(2)
                    ->schema([
                        Checkbox::make('download_xml')
                            ->label('Baixar XML')
                            ->default(true),
                        Checkbox::make('download_pdf')
                            ->label('Baixar PDF')
                            ->default(true),
                    ]),
            ])
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, download')
            ->action(function (Collection $records, array $data) {
                $baixarXml = (bool) ($data['download_xml'] ?? false);
                $baixarPdf = (bool) ($data['download_pdf'] ?? false);

                if (! $baixarXml && ! $baixarPdf) {
                    Notification::make()
                        ->title('Selecione pelo menos um tipo de arquivo')
                        ->body('Marque "Baixar XML" e/ou "Baixar PDF" para continuar.')
                        ->danger()
                        ->send();
                    throw new Halt;
                }

                $recordsWithXml = $records->filter(fn ($record) => ! empty($record->xml));

                if ($recordsWithXml->isEmpty()) {
                    Notification::make()
                        ->title('Nenhum XML disponível para download')
                        ->danger()
                        ->send();

                    throw new Halt;
                }

                DownloadXmlPdfCteEmLoteActionJob::dispatch(
                    $recordsWithXml,
                    $data,
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
