<?php

namespace App\Filament\Actions;

use App\Models\GeneralSetting;
use App\Models\ConhecimentoTransporteEletronico;
use App\Models\NotaFiscalEletronica;
use App\Models\NotaFiscalServico;
use App\Services\Tagging\TagSuggestionService;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ClassificarDocumentoMaisAplicadaEmLoteAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('classificar-mais-aplicada-em-lote')
            ->label('Classificar por ocorrência')
            ->requiresConfirmation()
            ->icon('heroicon-o-tag')
            ->modalHeading('Classificar por ocorrência')
            ->modalWidth('lg')
            ->modalDescription('Aplica automaticamente, para cada CNPJ emitente, a etiqueta mais utilizada no histórico. Documentos sem histórico serão ignorados.')
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
            ])
            ->action(function (Collection $records, array $data) {
                if ($records->isEmpty()) {
                    Notification::make()
                        ->title('Nenhum documento selecionado')
                        ->warning()
                        ->send();
                    throw new Halt;
                }

                $issuerId = currentIssuer()->id;
                $service = app(TagSuggestionService::class);

                $first = $records->first();
                if (! $first instanceof Model) {
                    Notification::make()
                        ->title('Falha ao processar os registros selecionados')
                        ->danger()
                        ->send();
                    throw new Halt;
                }

                $cnpjs = $records
                    ->map(function (Model $record) {
                        if ($record instanceof NotaFiscalEletronica) {
                            return $record->emitente_cnpj;
                        }

                        if ($record instanceof NotaFiscalServico) {
                            return $record->prestador_cnpj;
                        }

                        if ($record instanceof ConhecimentoTransporteEletronico) {
                            return $record->emitente_cnpj;
                        }

                        return null;
                    })
                    ->filter(fn ($cnpj) => filled($cnpj))
                    ->map(fn ($cnpj) => trim((string) $cnpj))
                    ->unique()
                    ->values()
                    ->all();

                if ($cnpjs === []) {
                    Notification::make()
                        ->title('Nenhum CNPJ emitente encontrado')
                        ->body('Os documentos selecionados não possuem CNPJ do emitente/prestador para realizar a classificação.')
                        ->warning()
                        ->send();
                    throw new Halt;
                }

                $isNfe = $records->every(fn (Model $record) => $record instanceof NotaFiscalEletronica);
                $isNfse = $records->every(fn (Model $record) => $record instanceof NotaFiscalServico);
                $isCte = $records->every(fn (Model $record) => $record instanceof ConhecimentoTransporteEletronico);

                if ($isNfe) {
                    $tagIdByCnpj = $service->mostAppliedTagIdForNfeEmitentes($cnpjs, $issuerId);
                } elseif ($isNfse) {
                    $tagIdByCnpj = $service->mostAppliedTagIdForNfsePrestadores($cnpjs, $issuerId);
                } elseif ($isCte) {
                    $tagIdByCnpj = $service->mostAppliedTagIdForCteEmitentes($cnpjs, $issuerId);
                } else {
                    Notification::make()
                        ->title('Tipo de documento não suportado')
                        ->danger()
                        ->send();
                    throw new Halt;
                }

                $total = $records->count();
                $applied = 0;
                $skipped = 0;

                $dataEntrada = $data['data_entrada'] ?? null;
                $fallbackDataEntrada = now();

                $records->each(function (Model $record) use (
                    $tagIdByCnpj,
                    $dataEntrada,
                    $fallbackDataEntrada,
                    &$applied,
                    &$skipped
                ) {
                    $cnpj = null;

                    if ($record instanceof NotaFiscalEletronica) {
                        $cnpj = $record->emitente_cnpj;
                    } elseif ($record instanceof NotaFiscalServico) {
                        $cnpj = $record->prestador_cnpj;
                    } elseif ($record instanceof ConhecimentoTransporteEletronico) {
                        $cnpj = $record->emitente_cnpj;
                    }

                    $cnpj = trim((string) ($cnpj ?? ''));
                    if ($cnpj === '') {
                        $skipped++;
                        return;
                    }

                    $tagId = $tagIdByCnpj[$cnpj] ?? null;
                    if (! $tagId) {
                        $skipped++;
                        return;
                    }

                    $record->retag((string) $tagId);

                    if ($dataEntrada !== null) {
                        $record->updateQuietly([
                            'data_entrada' => $dataEntrada,
                        ]);
                    } else {
                        $record->updateQuietly([
                            'data_entrada' => $fallbackDataEntrada,
                        ]);
                    }

                    $applied++;
                });

                if ($applied > 0) {
                    if ($isNfe) {
                        Cache::forget('tags_used_in_nfe_'.$issuerId);
                    }
                    if ($isCte) {
                        Cache::forget('tags_used_in_cte_'.$issuerId);
                    }
                }

                if ($applied === 0) {
                    Notification::make()
                        ->title('Nenhum documento classificado')
                        ->body("Total selecionado: {$total}. Ignorados: {$skipped}.")
                        ->warning()
                        ->send();
                    return;
                }

                $body = "Total selecionado: {$total}. Classificados: {$applied}. Ignorados: {$skipped}.";

                Notification::make()
                    ->title('Classificação em lote concluída')
                    ->body($body)
                    ->success()
                    ->send();
            });
    }
}
