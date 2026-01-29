<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ToggleEscrituracaoAction
{
    public static function make(): Action
    {

        return Action::make('toggle_apuracao')
            ->label('Alternar Apuração')
            ->icon('heroicon-o-currency-dollar')
            ->visible(function () {
                $user = Auth::user();
                if (! $user) {
                    return false;
                }

                return $user->hasRole('super-admin', 'admin', 'contabilidade')
                    && $user->hasPermission('marcar-documento-como-apurado');
            })
            ->requiresConfirmation()
            ->modalHeading(fn ($record) => $record->isApuradaParaEmpresa(Auth::user()->currentIssuer) ? 'Marcar nota como não apurada?' : 'Marcar nota como apurada?')
            ->modalDescription(fn ($record) => $record->isApuradaParaEmpresa(Auth::user()->currentIssuer)
                ? 'Tem certeza que deseja marcar esta nota fiscal como não apurada?'
                : 'Tem certeza que deseja marcar esta nota fiscal como apurada?')
            ->modalSubmitActionLabel('Confirmar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (Model $record) {

                $issuer = Auth::user()->currentIssuer;
                if (! method_exists($record, 'toggleApuracao') || ! method_exists($record, 'isApuradaParaEmpresa')) {
                    Notification::make()
                        ->title('Ação indisponível para este registro')
                        ->body('Este tipo de registro não suporta apuração.')
                        ->danger()
                        ->send();

                    return;
                }

                $isApurada = $record->toggleApuracao($issuer);

                Notification::make()
                    ->title($isApurada ? 'Nota fiscal apurada com sucesso!' : 'Nota fiscal desmarcada como apurada!')
                    ->body($isApurada ? 'A nota fiscal foi marcada como apurada.' : 'A nota fiscal foi marcada como não apurada.')
                    ->success()
                    ->send();
            });
    }
}
