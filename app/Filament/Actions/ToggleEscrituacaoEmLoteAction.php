<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
