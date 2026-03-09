<?php

namespace App\Filament\Resources\Issuers\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class RemoveCertificadoAction
{
    public static function make(): Action
    {
        return Action::make('remover_certificado')
            ->label('Remover Certificado')
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalHeading('Remover Certificado')
            ->modalDescription('Tem certeza que deseja remover o certificado desta empresa? Esta ação não pode ser desfeita.')
            ->modalSubmitActionLabel('Sim, remover')
            ->visible(fn (Model $record): bool => ! empty($record->certificado_content))
            ->action(function (Model $record) {
                $record->update([
                    'certificado_content' => null,
                    'senha_certificado' => null,
                    'validade_certificado' => null,
                ]);

                Notification::make()
                    ->title('Certificado removido com sucesso!')
                    ->success()
                    ->send();
            });
    }
}
