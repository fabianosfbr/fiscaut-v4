<?php

namespace App\Jobs\BulkAction;

use App\Models\SecureDownload;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
use NFePHP\DA\NFe\Danfe;
use setasign\Fpdi\Fpdi;
use ZipArchive;

class DownloadXmlPdfNfeEmLoteActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = false;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
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
            $this->records->loadMissing('tagged.tag');

            // Ensure the downloads directory exists with proper permissions
            $directory = 'downloads/' . now()->format('m-Y');
            $directoryPath = storage_path('app/private/' . $directory);

            if (!is_dir($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }

            $randomName = Str::random(8) . '.zip';
            $filename = $directory . '/' . $randomName;
            $pathFile = storage_path('app/private/' . $filename);

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
            $organizarPorEtiquetas = (bool) ($this->data['organizar_por_etiquetas'] ?? false);
            $adicionarEtiquetasPdf = (bool) ($this->data['adicionar_etiquetas_pdf'] ?? false);
            $erros = [];

            foreach ($this->records as $record) {
                try {
                    $subPath = '';
                    if ($organizarPorEtiquetas) {
                        $tagCount = count($record->tagged);
                        if ($tagCount > 1) {
                            $subPath = '#Multiplas Etiquetas/';
                        } elseif ($tagCount === 1) {
                            $tags = $record->tagNamesWithCode();
                            $subPath = ($tags[0] ?? 'Sem Etiqueta') . '/';
                        } else {
                            $subPath = 'Sem Etiqueta/';
                        }
                    }

                    $xml_content = gzuncompress($record->xml);

                    if ($baixarPdf) {
                        $danfe = new Danfe($xml_content);
                        $danfe->creditsIntegratorFooter(config('admin.footer_credits_danfe'), false);
                        $pdfContent = $danfe->render();
                        
                        if ($adicionarEtiquetasPdf && $record->tagged->isNotEmpty()) {
                            $pdfContent = $this->appendTagsPage($pdfContent, $record);
                        }

                        $pdfFileName = "{$record->chave}.pdf";
                        $zip->addFromString($subPath . $pdfFileName, $pdfContent);
                    }

                    if ($baixarXml) {
                        $xmlFileName = "{$record->chave}.xml";
                        $zip->addFromString($subPath . $xmlFileName, $xml_content);
                    }
                } catch (\Exception $e) {
                    $erros[] = "Erro ao gerar DANFE para a nota {$record->numero}: {$e->getMessage()}";
                    Log::warning('Error generating DANFE for note', [
                        'chave' => $record->chave,
                        'numero' => $record->numero,
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

            // Create secure download record
            $secureDownload = SecureDownload::create([
                'user_id' => $this->userId,
                'file_path' => $filename,
                'file_name' => 'nfe_' . now()->format('Ymd_His') . '.zip',
                'mime_type' => 'application/zip',
                'size' => filesize($pathFile),
                'job_class' => self::class,
                'expires_at' => now()->addDays(7),
            ]);

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
                        ->url(route('download', ['uuid' => $secureDownload->id])),
                ]);

            if (!empty($erros)) {
                $notification->body(
                    'Seus arquivos foram processados com sucesso, mas ocorreram alguns erros:' . 
                    PHP_EOL . implode(PHP_EOL, $erros)
                );
            }

            $notification->sendToDatabase(User::find($this->userId), isEventDispatched: true);

        } catch (\Exception $e) {
            Log::error('Error in DownloadXmlPdfNfeEmLoteActionJob', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->userId,
                'job_class' => self::class
            ]);

            // Re-throw the exception to fail the job appropriately
            throw $e;
        }
    }

    /**
     * Appends a page with tags to the existing PDF content.
     */
    protected function appendTagsPage(string $pdfContent, $record): string
    {
        try {
            // 1. Generate the tags page using DomPDF
            $tagsHtml = view('pdf.nfe-etiquetas', ['tagged' => $record->tagged])->render();
            $tagsPdfContent = Pdf::loadHTML($tagsHtml)->output();

            // 2. Use FPDI to merge the PDFs
            $tmpDanfe = tempnam(sys_get_temp_dir(), 'danfe_');
            $tmpTags = tempnam(sys_get_temp_dir(), 'tags_');
            
            file_put_contents($tmpDanfe, $pdfContent);
            file_put_contents($tmpTags, $tagsPdfContent);

            $pdf = new Fpdi();
            
            // Add original DANFE pages
            $pageCount = $pdf->setSourceFile($tmpDanfe);
            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }

            // Add tags page
            $pageCountTags = $pdf->setSourceFile($tmpTags);
            for ($i = 1; $i <= $pageCountTags; $i++) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }

            $mergedContent = $pdf->Output('S');

            // Cleanup
            unlink($tmpDanfe);
            unlink($tmpTags);

            return $mergedContent;
        } catch (\Exception $e) {
            Log::error('Error appending tags page to PDF', [
                'chave' => $record->chave,
                'error' => $e->getMessage()
            ]);
            return $pdfContent; // Return original if fails
        }
    }
}
