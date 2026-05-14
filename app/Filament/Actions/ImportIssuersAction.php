<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;

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
