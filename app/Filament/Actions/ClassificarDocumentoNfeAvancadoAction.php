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
use Illuminate\Support\Facades\Auth;

class ClassificarDocumentoNfeAvancadoAction
{
    public static function make()
    {
        return Action::make('classificar_avancado')
            ->label('Classificação Avançada')
            ->icon('heroicon-o-tag')
            ->modalHeading('Classificação Avançada da Nota Fiscal')
            ->modalWidth('7xl')
            ->modalDescription('Realize a classificação avançada para esta nota fiscal.')
            ->closeModalByClickingAway(false)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, etiquetar')
            ->schema(self::getFormSchema())
            ->action(function (array $data, Model $record) {

                $record->untag();

                // Aplica a etiqueta a nfe
                foreach ($data['etiquetas'] as $tag_apply) {
                    $record->tag($tag_apply['tag_id'], $tag_apply['valor'], $tag_apply['produtos']);
                }

                if (isset($data['data_entrada'])) {
                    $record->updateQuietly([
                        'data_entrada' => $data['data_entrada'],
                    ]);
                }
                Notification::make()
                    ->success()
                    ->title('Nota fiscal classificada com sucesso!')
                    ->body('Sua nota fiscal foi classificada com sucesso!')
                    ->duration(3000)
                    ->send();

                $record->refresh();   // recarrega os atributos do banco

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
                                $set('total_nfe', number_format($record->vNfe, 2, ',', '.'));
                            }
                        })
                        ->disabled(),

                    TextInput::make('valor_total_etiquetas')
                        ->label('Valor Total Etiquetas')
                        ->prefix('R$')
                        ->placeholder(function ($get, $set) {
                            $etiquetas = $get('etiquetas');
                            $totalCents = 0;
                            foreach ($etiquetas as $etiqueta) {
                                $totalCents += (int) preg_replace('/\D+/', '', (string) ($etiqueta['valor'] ?? ''));
                            }
                            $total = $totalCents / 100;
                            $set('valor_total_etiquetas', number_format($total, 2, ',', '.'));

                            return number_format($total, 2, ',', '.');
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
                            $issuerId = Auth::user()->currentIssuer->id;

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
                                ->options(CategoryTag::getAllEnabled(Auth::user()->currentIssuer->id)),

                            TextInput::make('valor')
                                ->prefix('R$')
                                ->live(onBlur: true)
                                ->required()
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                ->numeric(),

                            Select::make('produtos')
                                ->multiple()
                                ->reactive()
                                ->preload()
                                ->options(function ($record) {
                                    $produtos = $record->produtos ?? [];
                                    $itens = [];
                                    foreach ($produtos as $item) {

                                        if (is_array($item)) {
                                            $itens[$item['cProd']] = (string) $item['xProd'];
                                        }
                                    }

                                    return $itens;
                                })->columnSpan(1),
                        ])
                        ->columns(3)
                        ->columnSpan(3),
                ]),

        ];
    }
}
