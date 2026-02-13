<?php

namespace App\Jobs\BulkAction;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NFePHP\DA\CTe\Dacte;
use ZipArchive;

class DownloadXmlPdfCteEmLoteActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = false;

    public $timeout = 3600;

    public function __construct(
        public Collection $records,
        public array $data,
        public int $userId
    ) {
        $this->onQueue('low');
    }

    public function handle(): void
    {
        Storage::disk('public')->makeDirectory('downloads/'.now()->format('m-Y'));

        $filename = now()->format('m-Y').'/'.Str::random(8).'.zip';

        $pathFile = public_path('downloads/'.$filename);

        $zip = new ZipArchive;
        $zip->open($pathFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $baixarXml = (bool) ($this->data['download_xml'] ?? false);
        $baixarPdf = (bool) ($this->data['download_pdf'] ?? false);

        $erros = [];

        foreach ($this->records as $record) {
            try {
                if ($baixarPdf) {
                    $dacte = new Dacte(gzuncompress($record->xml));
                    $dacte->creditsIntegratorFooter(config('admin.footer_credits_danfe'), false);
                    $pdf = $dacte->render();
                    $pdfFileName = "{$record->chave}.pdf";
                    $zip->addFromString($pdfFileName, $pdf);
                }

                if ($baixarXml) {
                    $xmlFileName = "{$record->chave}.xml";
                    $xml_content = gzuncompress($record->xml);
                    $zip->addFromString($xmlFileName, $xml_content);
                }
            } catch (\Exception $e) {
                $erros[] = "Erro ao gerar DACTE para o CT-e {$record->nCTe}: {$e->getMessage()}";
            }
        }

        $zip->close();

        Notification::make()
            ->title('Arquivo disponível para download')
            ->icon('heroicon-o-arrow-down-circle')
            ->iconColor('success')
            ->body('Seus arquivos foram processados com sucesso')
            ->actions([
                Action::make('view')
                    ->label('Baixar arquivo')
                    ->button()
                    ->openUrlInNewTab()
                    ->url(url('').'/downloads/'.$filename),
            ])
            ->sendToDatabase(User::find($this->userId));
    }
}
