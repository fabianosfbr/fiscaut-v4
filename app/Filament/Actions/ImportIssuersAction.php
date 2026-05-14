<?php

namespace App\Filament\Actions;

use App\Jobs\ImportIssuersJob;
use App\Models\JobProgress;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportIssuersAction
{
    public static function make(): Action
    {
        return Action::make('import-issuers')
            ->label('Importar Empresas')
            ->icon('heroicon-o-arrow-up-tray')
            ->modalWidth(Width::FourExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(view('filament.modals.import-wizard-view'));
                    
    }

}
