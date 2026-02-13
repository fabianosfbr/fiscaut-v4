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
use ZipArchive;

class DownloadUploadFileBulkActionJob implements ShouldQueue
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

    /**
     * Execute the job.
     */
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

            if ($this->data['is_folder'] == true) {
                foreach ($this->records as $file) {
                    $tipoDocumentos = config('admin.doc_types');
                    if (count($file->tagged) > 1) {
                        $name = $tipoDocumentos[$file->doc_type->value] . '/' . '#Multiplas Etiquetas/' . basename($file->path);
                        $file_content = Storage::disk('public')->get($file->path);
                        $zip->addFromString($name, $file_content);
                    } else {
                        foreach ($file->tagNamesWithCode() as $path) {
                            $file_content = Storage::disk('public')->get($file->path);
                            $name = $tipoDocumentos[$file->doc_type->value] . '/' . $path . '/' . basename($file->path);
                            $zip->addFromString($name, $file_content);
                        }
                    }
                }
            } else {
                foreach ($this->records as $file) {

                    if (count($file->tagged) > 1) {
                        $name = '#Multiplas Etiquetas/' . basename($file->path);
                        $file_content = Storage::disk('public')->get($file->path);
                        $zip->addFromString($name, $file_content);
                    } else {
                        foreach ($file->tagNamesWithCode() as $path) {
                            $file_content = Storage::disk('public')->get($file->path);
                            $name = $path . '/' . basename($file->path);
                            $zip->addFromString($name, $file_content);
                        }
                    }
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

            $notification->sendToDatabase(User::find($this->userId), isEventDispatched: true);
        } catch (\Exception $e) {
            Log::error('Error in DownloadUploadFileBulkActionJob', [
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
