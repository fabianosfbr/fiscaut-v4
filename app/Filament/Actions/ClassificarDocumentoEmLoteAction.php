<?php

namespace App\Filament\Actions;

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\GeneralSetting;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ClassificarDocumentoEmLoteAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('toggle-escrituracao-em-lote')
            ->label('Classificar Documento')
            ->requiresConfirmation()
            ->icon('heroicon-o-tag')
            ->modalHeading('Classificar Documento Fiscal')
            ->modalWidth('lg')
            ->modalDescription('Selecione uma etiqueta para este documento fiscal.')
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
                        $issuerId = currentIssuer()->id;

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
                    ->options(CategoryTag::getAllEnabled(currentIssuer()->id)),
            ])
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, download')
            ->action(function (Collection $records, array $data) {
                $records->each(function (Model $record) use ($data) {
                    $record->retag($data['tag_id']);

                    if (isset($data['data_entrada'])) {
                        $record->updateQuietly([
                            'data_entrada' => $data['data_entrada'],
                        ]);
                    } else {
                        $record->updateQuietly([
                            'data_entrada' => now(),
                        ]);
                    }
                });
            });
    }
}
