<?php

namespace App\Filament\Actions;

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\GeneralSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

                Notification::make()
                    ->success()
                    ->title('Documento classificado com sucesso!')
                    ->body('Seu documento fiscal foi classificada com sucesso!')
                    ->duration(3000)
                    ->send();
            });
    }
}
