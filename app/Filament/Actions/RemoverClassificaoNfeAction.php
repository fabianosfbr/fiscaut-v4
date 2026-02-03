<?php

namespace App\Filament\Actions;

use App\Models\ConhecimentoTransporteEletronico;
use App\Models\GeneralSetting;
use App\Models\NotaFiscalEletronica;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RemoverClassificaoNfeAction
{
    public static function make()
    {
        return Action::make('remover-classificar')
            ->label('Remover Classificação')
            ->icon('heroicon-o-x-mark')
            ->requiresConfirmation()
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, remover')
            ->action(function (array $data, Model $record) {

                self::removeSameTagToCte($record);

                $record->untag();
                $record->update([
                    'data_entrada' => null,
                ]);

                Cache::forget('tags_used_in_nfe_'.Auth::user()->currentIssuer->id);

                Notification::make()
                    ->success()
                    ->title('Classificação removida com sucesso!')
                    ->body('A classificação do documento fiscal foi removida com sucesso!')
                    ->duration(3000)
                    ->send();
            });
    }

    private static function removeSameTagToCte(Model $record): void
    {
        $isClassificarCteVinculadoANfe = GeneralSetting::getValue(
            name: 'configuracoes_gerais',
            key: 'isClassificarCteVinculadoANfe',
            default: false,
            issuerId: Auth::user()->currentIssuer->id
        );

        if ($isClassificarCteVinculadoANfe && $record instanceof NotaFiscalEletronica) {
            $nfeChave = trim((string) $record->chave);

            $ctes = ConhecimentoTransporteEletronico::query()
                ->whereNfeChave($nfeChave)
                ->get();

            if ($ctes->isNotEmpty()) {
                foreach ($ctes as $cte) {
                    $cte->untag();
                    $cte->update([
                        'data_entrada' => null,
                    ]);
                }
            }

            Cache::forget('tags_used_in_cte_'.Auth::user()->currentIssuer->id);
        }
    }
}
