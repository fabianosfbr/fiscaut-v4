<?php

namespace App\Filament\Resources\UploadFileManagers\Actions;

use App\Models\UploadFile;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DownloadFileAction
{
    public static function make()
    {
        return Action::make('download')
            ->label('Download')
            ->requiresConfirmation()
            ->icon(Heroicon::ArrowDown)
            ->url(fn (UploadFile $record) => route('upload-file.preview', $record))
            ->openUrlInNewTab();
    }
}
