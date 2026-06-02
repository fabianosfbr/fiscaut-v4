<?php

namespace App\Filament\Actions;

use App\Integrations\DominioSistemas\Services\OrquestradorService;
use App\Jobs\GerarArquivoDominio;
use App\Models\NotaFiscalEletronica;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class GerarTxtIntegracaoDominioSistema
{
    public static function make(): BulkAction
    {
        return BulkAction::make('gerar-txt-integracao-dominio-sistema')
            ->label('Integração Domínio Sistema')
            ->requiresConfirmation()
            ->icon('heroicon-o-tag')
            ->modalHeading('Gerar Arquivo de Integração Domínio Sistema')
            ->modalWidth('lg')
            ->modalDescription('Todas as notas fiscais selecionadas e etiquetadas serão processadas em segundo plano. Você receberá uma notificação quando o arquivo estiver pronto.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, gerar arquivo')
            ->action(function (Collection $records) {
                $issuer = currentIssuer();
                if (!$issuer) {
                    Notification::make()
                        ->title('Nenhuma empresa selecionada')
                        ->danger()
                        ->send();

                    return;
                }

                // Validar se todas as NFs têm etiqueta
                $notasSemEtiqueta = $records->filter(fn($nf) => $nf->tagged->isEmpty());
                if ($notasSemEtiqueta->isNotEmpty()) {
                    $nfs = $notasSemEtiqueta->map(fn($nf) => "NF {$nf->nNF}")->implode(', ');
                    Notification::make()
                        ->title("As seguintes notas não possuem etiqueta: {$nfs}")
                        ->warning()
                        ->send();

                    return;
                }

                $notaIds = $records->pluck('id')->toArray();

                // $notas = NotaFiscalEletronica::whereIn('id', $notaIds)
                //     ->with('tagged.tag')
                //     ->get();

                // try {
                //     $orquestrador = new OrquestradorService($issuer);
                //     $resultado = $orquestrador->gerarTxt($notas);
                //     dd($resultado);
                // } catch (\Exception $e) {
                // }


                // Dispatch job assíncrono
                 GerarArquivoDominio::dispatch($notaIds, $issuer->id);

                Notification::make()
                    ->title('Processamento iniciado!')
                    ->body(count($notaIds) . ' NFs enviadas para processamento. Você receberá uma notificação quando o arquivo estiver pronto.')
                    ->success()
                    ->send();
            });
    }
}
