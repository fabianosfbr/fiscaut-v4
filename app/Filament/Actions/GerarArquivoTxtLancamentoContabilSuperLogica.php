<?php

namespace App\Filament\Actions;

use App\Models\ImportarLancamentoContabil;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GerarArquivoTxtLancamentoContabilSuperLogica
{
    public static function make(): Action
    {
        return Action::make('gerar-arquivo-txt-lancamento-contabil-geral')
            ->label('Gerar Arquivo TXT')
            ->icon('heroicon-o-document-text')
            ->modalWidth('lg')
            ->modalHeading('Gerar Arquivo TXT')
            ->modalDescription('Insira os dados para gerar o arquivo TXT.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, gerar arquivo')
            ->action(function (array $data, $action) {
                $user = Auth::user();
                $issuerId = currentIssuer($user)->id;
                $lancamentos = ImportarLancamentoContabil::where('issuer_id', $issuerId)
                    ->where('user_id', $user->id)
                    ->where('metadata->type', 'super-logica')
                    ->whereNotNull('data')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($lancamentos->count() == 0) {

                    Notification::make()
                        ->title('Erro ao gerar lançamento')
                        ->body('Não existe registros para serem processados')
                        ->success()
                        ->send();

                    $action->halt();
                }

                $filename = Str::random(20).'.txt';

                $txtContent = self::gerarRelatorio($lancamentos, $data, ';');

                $txtContentAnsi = mb_convert_encoding($txtContent, 'Windows-1252', 'UTF-8');

                Notification::make()
                    ->title('Exportação iniciada')
                    ->body('A exportação foi iniciada e as linhas selecionadas serão processadas em segundo plano')
                    ->success()
                    ->send();

                return response()->streamDownload(function () use ($txtContentAnsi) {
                    echo $txtContentAnsi;
                }, $filename);

            });
    }

    public static function gerarRelatorio(Collection $lancamentos, array $data = [], string $separador = ';'): string
    {

        $linhas = $lancamentos
            ->map(fn ($lancamento) => $this->formatarConteudo($lancamento, $data, $separador));

        return $linhas->implode(PHP_EOL).PHP_EOL;
    }

    private function formatarConteudo($lancamento, array $params, string $separador): string
    {

        $data = $lancamento->data?->format('d/m/Y') ?? '';
        $valorFormatado = number_format(abs($lancamento->valor), 2, ',', '');

        return implode($separador, [
            $data,
            $lancamento->debito,
            $lancamento->credito,
            $valorFormatado,
            $lancamento->metadata['codigo_historico'] ?? '',
            $lancamento->historico,
        ]);
    }
}
