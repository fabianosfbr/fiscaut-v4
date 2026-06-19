<?php

namespace App\Filament\Actions;

use App\Models\NotaFiscalEletronica;
use App\Services\ValidacaoTributaria\ValidacaoTributariaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ValidarTributacaoAction
{
    public static function make(): Action
    {
        return Action::make('validar_tributacao')
            ->label('Validar Tributação')
            ->icon('heroicon-o-shield-check')
            ->visible(fn (Model $record): bool => $record instanceof NotaFiscalEletronica)
            ->action(function (Model $record) {
                $issuer = currentIssuer();
                $service = app(ValidacaoTributariaService::class);
                $total = $service->validarEPersistir($record, $issuer);

                if ($total > 0) {
                    Notification::make()
                        ->title("Validação concluída — {$total} inconsistência(s) encontrada(s)")
                        ->warning()
                        ->persistent()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Nenhuma inconsistência encontrada')
                        ->success()
                        ->send();
                }
            });
    }
}
