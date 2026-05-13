<?php

namespace App\Filament\Actions;

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\GeneralSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Illuminate\Database\Eloquent\Model;
use Closure;

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
            ->action(function (array $data, Model $record, $action) {
                $produtosNfe = $record->produtos ?? [];
                $produtosMarcados = [];

                foreach ($data['etiquetas'] as $tag_apply) {
                    $produtosSelecionados = $tag_apply['produtos'] ?? [];
                    $produtosMarcados = array_merge($produtosMarcados, $produtosSelecionados);
                }

                $produtosMarcados = array_unique($produtosMarcados);
                $totalProdutosNfe = count($produtosNfe);
                $totalProdutosMarcados = count($produtosMarcados);

                if (!empty($produtosMarcados) && $totalProdutosNfe > 0 && $totalProdutosMarcados < $totalProdutosNfe) {
                    Notification::make()
                        ->warning()
                        ->title('Não foi possível etiquetar')
                        ->body("Ao selecionar produtos, apenas {$totalProdutosMarcados} etiqueado(s). Por favor, selecione todos os produtos da nota fiscal.")
                        ->send();

                    $action->halt();
                    return;
                }

                $record->untag();
                foreach ($data['etiquetas'] as $tag_apply) {
                    $produtosSelecionados = $tag_apply['produtos'] ?? [];
                    $valorEtiqueta = $tag_apply['valor'] ?? null;

                    $record->tag($tag_apply['tag_id'], $valorEtiqueta, $produtosSelecionados);
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
                    ->send();

                $record->refresh();
            });
    }

    private static function calcularValorProdutos(array $produtosNfe, array $produtosSelecionados): float
    {
        $total = 0.0;

        foreach ($produtosNfe as $item) {
            $nItem = $item['nItem'] ?? $item['cProd'];
            foreach ($produtosSelecionados as $prodSelecionado) {
                if ($nItem == $prodSelecionado) {
                    $vProd = (float) ($item['vProd'] ?? 0);
                    $vIcmsSt = (float) ($item['impostos']['vICMSST'] ?? 0);
                    $vIpi = (float) ($item['impostos']['vIPI'] ?? 0);
                    $total += $vProd + $vIcmsSt + $vIpi;
                }
            }
        }

        return $total;
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
                        ->disabled()
                        ->hiddenOn('edit')
                        ->columnSpan(1)
                        ->placeholder(function ($get, $set) {
                            $fields = $get('etiquetas');
                            ds($fields);
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
                            fn($get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                $value = str_replace(',', '.', str_replace('.', '', $value));
                                $totalNfe = str_replace(',', '.', str_replace('.', '', $get('total_nfe')));
                                if ($value != $totalNfe) {
                                    $fail('O valor deve ser igual o valor da nota');
                                }
                            },
                        ])
                        ->disabled(),
                    // TextInput::make('valor_total_etiquetas')
                    //     ->label('Valor Total Etiquetas')
                    //     ->prefix('R$')
                    //     ->placeholder(function ($get, $set) {
                    //         $etiquetas = $get('etiquetas');
                    //         $totalCents = 0;
                    //         foreach ($etiquetas as $etiqueta) {
                    //             $valorEtiqueta = $etiqueta['valor'] ?? '';
                    //             $valorLimpo = preg_replace('/[^\d]/', '', $valorEtiqueta);
                    //             $totalCents += (int) ($valorLimpo ?: 0);
                    //             ds($totalCents);
                    //         }
                    //         $total = $totalCents / 100;
                    //         $set('valor_total_etiquetas', number_format($total, 2, ',', '.'));
                    //         return number_format($total, 2, ',', '.');
                    //     })
                    //     ->rules([
                    //         fn($get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    //             $value = str_replace(',', '.', str_replace('.', '', $value));
                    //             $totalNfe = str_replace(',', '.', str_replace('.', '', $get('total_nfe')));
                    //             if ($value != $totalNfe) {
                    //                 $fail('O valor deve ser igual o valor da nota');
                    //             }
                    //         },
                    //     ])
                    //     ->disabled(),
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
                            Select::make('produtos')
                                ->multiple()
                                ->reactive()
                                ->helperText('Selecione os produtos para associar à etiqueta')
                                ->preload()
                                ->options(function ($record) {
                                    $produtos = $record->produtos ?? [];
                                    $itens = [];
                                    foreach ($produtos as $item) {
                                        if (is_array($item)) {
                                            $produtoLabel = $item['xProd'] ?? $item['cProd'];
                                            $nItem = $item['nItem'] ?? $item['cProd'];
                                            $vProd = (float) ($item['vProd'] ?? 0);
                                            $vIcmsSt = (float) ($item['impostos']['vICMSST'] ?? 0);
                                            $vIpi = (float) ($item['impostos']['vIPI'] ?? 0);
                                            $valorTotal = $vProd + $vIcmsSt + $vIpi;
                                            $valorProd = ' - R$ ' . number_format($valorTotal, 2, ',', '.');
                                            $itens[$nItem] = (string) $produtoLabel . $valorProd;
                                        }
                                    }

                                    return $itens;
                                })
                                ->afterStateUpdated(function ($set, $get, $state, $record) {
                                    $produtosNfe = $record->produtos ?? [];

                                    $produtosSelecionados = $state ?? [];

                                    if (!empty($produtosSelecionados) && !empty($produtosNfe)) {
                                        $valorCalculado = self::calcularValorProdutos($produtosNfe, $produtosSelecionados);

                                        $set('valor', $valorCalculado);
                                    }
                                })
                                ->columnSpan(1),
                        ])
                        ->columns(3)
                        ->columnSpan(3),
                ]),
        ];
    }
}
