<?php

namespace App\Filament\Actions;

use App\Models\NotaFiscalEletronica;
use App\Services\ValidacaoTributaria\ValidacaoTributariaService;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class ValidarTributacaoEmLoteAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('validar_tributacao_em_lote')
            ->label('Validar Tributação')
            ->icon('heroicon-o-shield-check')
            ->requiresConfirmation()
            ->modalHeading('Validar Tributação em Lote')
            ->modalDescription('Deseja validar a tributação de todas as NF-e selecionadas?')
            ->modalSubmitActionLabel('Validar')
            ->action(function (Collection $records) {
                $issuer = currentIssuer();
                $service = app(ValidacaoTributariaService::class);
                $totalInconsistencias = 0;
                $totalProcessadas = 0;

                foreach ($records as $record) {
                    if ($record instanceof NotaFiscalEletronica) {
                        $totalInconsistencias += $service->validarEPersistir($record, $issuer);
                        $totalProcessadas++;
                    }
                }

                if ($totalInconsistencias > 0) {
                    Notification::make()
                        ->title("Validação concluída — {$totalProcessadas} NF-e processadas, {$totalInconsistencias} inconsistência(s) encontrada(s)")
                        ->warning()
                        ->persistent()
                        ->send();
                } else {
                    Notification::make()
                        ->title("Validação concluída — {$totalProcessadas} NF-e processadas, nenhuma inconsistência encontrada")
                        ->success()
                        ->send();
                }
            });
    }
}
