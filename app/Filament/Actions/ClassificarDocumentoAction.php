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
use App\Filament\Forms\Components\SelectTagGrouped;

class ClassificarDocumentoAction
{

    public static function make()
    {
        return Action::make('classificar')
            ->label('Classificar Documento')
            ->icon('heroicon-o-tag')
            ->modalHeading('Classificar Nota Fiscal')
            ->modalWidth('lg')
            ->modalDescription('Selecione uma etiqueta para esta nota fiscal.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, etiquetar')
            ->schema([

                DatePicker::make('data_entrada')
                    ->label('Data Entrada')
                    ->required()
                    ->format('Y-m-d')
                    ->weekStartsOnSunday()
                    ->default(now())
                    ->displayFormat('d/m/Y')
                    ->visible(function () {
                        $issuerId = Auth::user()->currentIssuer->id;

                        return GeneralSetting::getValue(
                            name: 'configuracoes_gerais',
                            key: 'isNfeClassificarNaEntrada',
                            default: false,
                            issuerId: $issuerId
                        );
                    }),

                SelectTagGrouped::make('tag_id')
                    ->label('Etiqueta')
                    ->multiple(false)
                    ->required()
                    ->options(CategoryTag::getAllEnabled(Auth::user()->currentIssuer->id)),
            ])
            ->action(function (array $data, Model $record) {
                $record->retag($data['tag_id']);

                if (isset($data['data_entrada'])) {
                    $record->updateQuietly([
                        'data_entrada' => $data['data_entrada'],
                    ]);
                }

                Cache::forget('tags_used_in_nfe_' . Auth::user()->currentIssuer->id);

                Notification::make()
                    ->success()
                    ->title('Documento classificado com sucesso!')
                    ->body('Seu documento fiscal foi classificada com sucesso!')
                    ->duration(3000)
                    ->send();
            });
    }
}
