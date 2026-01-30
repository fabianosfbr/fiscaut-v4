<?php

namespace App\Filament\Actions;

use App\Models\NotaFiscalEletronica;
use App\Services\Tagging\TagSuggestionService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class SugerirEtiquetaAction
{
    public static function make()
    {
        return Action::make('suggest_tags')
            ->label('Sugerir etiquetas')
            ->icon(Heroicon::LightBulb)
            ->tooltip('Sugerir etiquetas')
            ->modalDescription('As sugestões são baseadas no histórico de etiquetas aplicadas aos documentos emitidos pela empresa.')
            ->visible(fn (NotaFiscalEletronica $record): bool => filled($record->emitente_cnpj) && $record->tagged->isEmpty())
            ->modalHeading('Etiquetas aplicadas')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalWidth('lg')
            ->modalContent(function (NotaFiscalEletronica $record) {
                $suggestions = app(TagSuggestionService::class)->forNfeEmitente(
                    emitenteCnpj: $record->emitente_cnpj,
                );

                return view('filament.modals.nfe-tag-suggestions', [
                    'record' => $record,
                    'suggestions' => $suggestions,
                ]);
            });
    }
}
