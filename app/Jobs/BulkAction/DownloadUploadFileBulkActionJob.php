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
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Storage::disk('public')->makeDirectory('downloads/'.now()->format('m-Y'));

        $filename = now()->format('m-Y').'/'.Str::random(8).'.zip';

        $pathFile = public_path('downloads/'.$filename);

        $zip = new ZipArchive;
        $zip->open($pathFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($this->data['is_folder'] == true) {
            foreach ($this->records as $file) {
                $tipoDocumentos = config('admin.doc_types');
                if (count($file->tagged) > 1) {
                    $name = $tipoDocumentos[$file->doc_type->value].'/'.'#Multiplas Etiquetas/'.basename($file->path);
                    $file_content = Storage::disk('public')->get($file->path);
                    $zip->addFromString($name, $file_content);
                } else {
                    foreach ($file->tagNamesWithCode() as $path) {
                        $file_content = Storage::disk('public')->get($file->path);
                        $name = $tipoDocumentos[$file->doc_type->value].'/'.$path.'/'.basename($file->path);
                        $zip->addFromString($name, $file_content);
                    }
                }
            }
        } else {
            foreach ($this->records as $file) {

                if (count($file->tagged) > 1) {
                    $name = '#Multiplas Etiquetas/'.basename($file->path);
                    $file_content = Storage::disk('public')->get($file->path);
                    $zip->addFromString($name, $file_content);
                } else {
                    foreach ($file->tagNamesWithCode() as $path) {
                        $file_content = Storage::disk('public')->get($file->path);
                        $name = $path.'/'.basename($file->path);
                        $zip->addFromString($name, $file_content);
                    }
                }
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
