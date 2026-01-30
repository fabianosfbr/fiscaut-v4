<?php

namespace App\Filament\Actions;

use ZipArchive;
use NFePHP\DA\NFe\Danfe;
use App\Models\CategoryTag;
use App\Models\GeneralSetting;
use Filament\Actions\BulkAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Grid;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Checkbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Forms\Components\SelectTagGrouped;
use App\Jobs\BulkAction\DownloadXmlPdfNfeEmLoteActionJob;

class ToggleEscrituacaoEmLoteAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('toggle_escrituracao_em_lote')
            ->label('Alternar Escrituração')
            ->icon('heroicon-o-document-check')
            ->requiresConfirmation()
            ->modalHeading('Alternar Escrituração')
            ->modalWidth('lg')
            ->modalDescription('Confirme a alternância de escrituração para os documentos fiscais selecionados.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, alternar')
            ->action(function (Collection $records) {
                $issuer = Auth::user()->currentIssuer;
                $records->each(function (Model $record) use ($issuer) {
                    $record->toggleApuracao($issuer);
                });
            })
            ->successNotificationTitle('Escrituração alternada com sucesso')
            ->failureNotificationTitle(function (int $successCount, int $totalCount): string {
                if ($successCount) {
                    return "{$successCount} of {$totalCount} documentos fiscais escrituradas";
                }

                return 'Erro ao alterar a escrituração das documentos fiscais';
            });
    }
}
