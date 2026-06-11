<?php

namespace App\Filament\Actions;

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\GeneralSetting;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Model;

class ClassificarDocumentoNfseAvancadoAction
{
    public static function make()
    {
        return Action::make('classificar_nfse_avancado')
            ->label('Classificação Avançada')
            ->icon('heroicon-o-tag')
            ->modalHeading('Classificação Avançada da Nota Fiscal de Serviço')
            ->modalWidth('7xl')
            ->modalDescription('Realize a classificação avançada para esta nota fiscal de serviço.')
            ->closeModalByClickingAway(false)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, etiquetar')
            ->schema(self::getFormSchema())
            ->action(function (array $data, Model $record, $action) {
               
                $record->untag();
                foreach ($data['etiquetas'] as $tag_apply) {                    
                    $valorEtiqueta = $tag_apply['valor'] ?? null;

                    $record->tag($tag_apply['tag_id'], $valorEtiqueta);
                }

                if (isset($data['data_entrada'])) {
                    $record->updateQuietly([
                        'data_entrada' => $data['data_entrada'],
                    ]);
                }
                Notification::make()
                    ->success()
                    ->title('Nota fiscal de serviço classificada com sucesso!')
                    ->body('Sua nota fiscal de serviço foi classificada com sucesso!')
                    ->send();

                $record->refresh();
            });
    }


    private static function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    TextInput::make('total_nfe')
                        ->label('Valor da NFe')
                        ->prefix('R$')
                        ->afterStateHydrated(function ($set, $record) {
                            if ($record) {
                                $set('total_nfe', number_format($record->valor_servico, 2, ',', '.'));
                            }
                        })
                        ->disabled(),
                    TextInput::make('valor_total_etiquetas')
                        ->label('Valor Total Etiquetas')
                        ->prefix('R$')
                        ->disabled()
                        ->hiddenOn('edit')
                        ->columnSpan(1)
                        ->placeholder(function ($get, $set) {
                            $fields = $get('etiquetas');
                            $sum = 0.0;
                            if (is_array($fields)) {
                                foreach ($fields as $field) {
                                    if (isset($field['valor'])) {
                                        $valor = $field['valor'];
                                        $sum += floatval($valor);
                                    }
                                }
                            }
                            $set('valor_total_etiquetas', number_format($sum, 2, ',', '.'));

                            return number_format($sum, 2, ',', '.');
                        })
                        ->rules([
                            fn ($get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                $value = str_replace(',', '.', str_replace('.', '', $value));
                                $totalNfe = str_replace(',', '.', str_replace('.', '', $get('total_nfe')));
                                if ($value != $totalNfe) {
                                    $fail('O valor deve ser igual o valor da nota');
                                }
                            },
                        ])
                        ->disabled(),
                    
                    DatePicker::make('data_entrada')
                        ->label('Data Entrada')
                        ->visible(function () {
                            $issuerId = currentIssuer()->id;

                            return GeneralSetting::getValue(
                                name: 'configuracoes_gerais',
                                key: 'isNfeClassificarNaEntrada',
                                default: false,
                                issuerId: $issuerId
                            );
                        })
                        ->default(now())
                        ->required(),
                    Repeater::make('etiquetas')
                        ->label('Etiqueta')
                        ->live()
                        ->schema([
                            SelectTagGrouped::make('tag_id')
                                ->label('Etiqueta')
                                ->multiple(false)
                                ->required()
                                ->options(CategoryTag::getAllEnabled(currentIssuer()->id)),
                            TextInput::make('valor')
                                ->prefix('R$')
                                ->live(onBlur: true)
                                ->required()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                ->numeric(),
                            
                        ])
                        ->columns(3)
                        ->columnSpan(3),
                ]),
        ];
    }
}
