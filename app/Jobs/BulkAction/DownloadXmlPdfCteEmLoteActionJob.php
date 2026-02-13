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
use Illuminate\Support\Facades\Log;
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
        try {
            // Ensure the downloads directory exists with proper permissions
            $directory = 'downloads/' . now()->format('m-Y');
            $directoryPath = storage_path('app/public/' . $directory);

            if (!is_dir($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }

            $filename = $directory . '/' . Str::random(8) . '.zip';
            $pathFile = storage_path('app/public/' . $filename);

            $zip = new ZipArchive;
            $result = $zip->open($pathFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($result !== true) {
                Log::error('Failed to create zip archive', [
                    'result_code' => $result,
                    'path_file' => $pathFile,
                    'job_class' => self::class,
                    'user_id' => $this->userId
                ]);

                throw new \Exception("Could not create zip file. Error code: {$result}");
            }

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
                    Log::warning('Error generating DACTE for CTe', [
                        'chave' => $record->chave,
                        'nCTe' => $record->nCTe,
                        'error' => $e->getMessage(),
                        'job_class' => self::class
                    ]);
                }
            }

            // Properly close the zip archive with error checking
            $closeResult = $zip->close();
            if (!$closeResult) {
                Log::error('Failed to close zip archive', [
                    'path_file' => $pathFile,
                    'job_class' => self::class,
                    'user_id' => $this->userId
                ]);

                throw new \Exception('Could not close zip file properly');
            }

            // Send notification to user
            $notification = Notification::make()
                ->title('Arquivo disponível para download')
                ->icon('heroicon-o-arrow-down-circle')
                ->iconColor('success')
                ->body('Seus arquivos foram processados com sucesso')
                ->actions([
                    Action::make('view')
                        ->label('Baixar arquivo')
                        ->button()
                        ->openUrlInNewTab()
                        ->url(asset('storage/' . $filename)),
                ]);

            if (!empty($erros)) {
                $notification->body(
                    'Seus arquivos foram processados com sucesso, mas ocorreram alguns erros:' .
                        PHP_EOL . implode(PHP_EOL, $erros)
                );
            }

            $notification->sendToDatabase(User::find($this->userId), isEventDispatched: true);
        } catch (\Exception $e) {
            Log::error('Error in DownloadXmlPdfCteEmLoteActionJob', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId,
                'job_class' => self::class
            ]);

            // Re-throw the exception to fail the job appropriately
            throw $e;
        }
    }
}
