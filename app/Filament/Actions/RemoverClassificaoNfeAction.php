<?php

namespace App\Filament\Actions;

use App\Models\CategoryTag;
use Filament\Actions\Action;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use App\Models\ConhecimentoTransporteEletronico;
use App\Filament\Forms\Components\SelectTagGrouped;

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


                $ctes = ConhecimentoTransporteEletronico::whereJsonContains('nfe_chave', ['chave' => $record->chave])
                    ->where('tomador_cnpj', $record->destinatario_cnpj)->get();
                if (isset($ctes)) {
                    foreach ($ctes as $cte) {
                        $cte->untag();
                        $cte->update([
                            'data_entrada' => null,
                        ]);
                    }
                }

                $record->untag();
                $record->update([
                    'data_entrada' => null,
                ]);

                Cache::forget('tags_used_in_nfe_' . Auth::user()->currentIssuer->id);

                Notification::make()
                    ->success()
                    ->title('Classificação removida com sucesso!')
                    ->body('A classificação do documento fiscal foi removida com sucesso!')
                    ->duration(3000)
                    ->send();
            });
    }
}
