<?php

namespace App\Filament\Actions;

use App\Models\CategoryTag;
use Filament\Actions\Action;
use App\Models\GeneralSetting;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use App\Models\ConhecimentoTransporteEletronico;
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

                self::applySameTagToCte($record, $data);

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

    private static function applySameTagToCte(Model $record, array $data): void
    {
        $isClassificarCteVinculadoANfe = GeneralSetting::getValue(
            name: 'configuracoes_gerais',
            key: 'isClassificarCteVinculadoANfe',
            default: false,
            issuerId: Auth::user()->currentIssuer->id
        );

        if ($isClassificarCteVinculadoANfe && $record instanceof NotaFiscalEletronica) {
            //Aplica a mesma tag ao CTe do tomador
            $nfeChave = trim((string) $record->chave);

            $ctes = ConhecimentoTransporteEletronico::query()
                ->whereNfeChave($nfeChave)
                ->get();


            if ($ctes->isNotEmpty()) {
                foreach ($ctes as $cte) {
                    $cte->untag();
                    $cte->tag($data['tag_id'], $cte->vCTe);
                    if (isset($data['data_entrada'])) {
                        $cte->update([
                            'data_entrada' => $data['data_entrada'],
                        ]);
                    }
                }
            }

            Cache::forget('tags_used_in_cte_' . Auth::user()->currentIssuer->id);
        }
    }
}
