<?php

namespace App\Filament\Actions;

use App\Filament\Forms\Components\SelectPlanoDeConta;
use App\Models\HistoricoContabil;
use App\Models\ImportarLancamentoContabil;
use App\Models\PlanoDeConta;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GerarArquivoTxtLancamentoContabilGeral
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
            ->schema([
                Checkbox::make('is_exist')
                    ->label('Gerar somente lançamentos com vinculo')
                    ->live()
                    ->default(true),

                Checkbox::make('limpar_lancamentos')
                    ->label('Limpar lançamentos após geração do arquivo')
                    ->default(false),

                Fieldset::make('Registros sem lançamento')
                    ->visible(fn (callable $get) => $get('is_exist') === false)
                    ->schema([
                        SelectPlanoDeConta::make('conta_contabil')
                            ->label('Conta contabil')
                            ->required()
                            ->columnSpan(2),

                        Select::make('codigo_historico')
                            ->label('Cód. Histórico')
                            ->required()
                            ->options(function () {

                                $values = HistoricoContabil::where('issuer_id', currentIssuer()->id)
                                    ->orderBy('codigo', 'asc')
                                    ->get()
                                    ->map(function ($item) {
                                        $item->codigo_descricao = $item->codigo.' | '.$item->descricao;

                                        return $item;
                                    })

                                    ->pluck('codigo_descricao', 'id');

                                return $values;
                            })
                            ->columnSpan(2),

                    ]),
            ])
            ->action(function (array $data, $action) {
                $user = Auth::user();
                $issuerId = currentIssuer($user)->id;
                $lancamentos = ImportarLancamentoContabil::where('issuer_id', $issuerId)
                    ->where('user_id', $user->id)
                    ->where('valor', '!=', 0)
                    ->when($data['is_exist'], fn ($query) => $query->where('is_exist', $data['is_exist'])) // Aplica o filtro apenas se is_exist for true
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

                $filename = now()->format('m-Y').'/'.Str::random(8).'.txt';

                if (isset($data['conta_contabil'])) {
                    $conta_contabil = PlanoDeConta::where('issuer_id', $issuerId)
                        ->where('codigo', $data['conta_contabil'])->first();

                    $data['descricao_conta_contabil'] = $conta_contabil?->nome;
                }

                $txtContent = $this->gerarRelatorio($lancamentos, $data, ';');

                $txtContentAnsi = mb_convert_encoding($txtContent, 'Windows-1252', 'UTF-8');

                Storage::disk('downloads-files')->put($filename, $txtContentAnsi);

                Notification::make()
                    ->title('Exportação iniciada')
                    ->body('A exportação foi iniciada e as linhas selecionadas serão processadas em segundo plano')
                    ->success()
                    ->send();

                if ($data['limpar_lancamentos']) {
                    ImportarLancamentoContabil::where('issuer_id', $issuerId)
                        ->where('user_id', $user->id)
                        ->delete();
                }

                return response()->download(public_path('/downloads/'.$filename));
            });
    }

    public function gerarRelatorio(Collection $lancamentos, array $data = [], string $separador = ';'): string
    {

        $linhas = $lancamentos
            ->map(fn ($lancamento) => $this->formatarConteudo($lancamento, $data, $separador));

        return $linhas->implode(PHP_EOL).PHP_EOL;
    }

    private function formatarConteudo($lancamento, array $params, string $separador): string
    {

        $data = $lancamento->data->format('d/m/Y');
        $valorFormatado = number_format(abs($lancamento->valor), 2, ',', '');

        $semLancamento = false;

        // Não possui lançamento
        if ($lancamento->is_exist == false) {

            $semLancamento = true;
            if (is_null($lancamento->credito)) {

                $metadata = $lancamento->metadata;
                $metadata['descricao_credito'] = $params['descricao_conta_contabil'];

                $lancamento->credito = $params['conta_contabil'];
                $lancamento->metadata = $metadata;
            } else {

                $metadata = $lancamento->metadata;
                $metadata['descricao_debito'] = $params['descricao_conta_contabil'];

                $lancamento->debito = $params['conta_contabil'];
                $lancamento->metadata = $metadata;
            }
        }

        return implode($separador, [
            $data,
            $lancamento->debito,
            $lancamento->credito,
            $valorFormatado,
            0,
            $lancamento->historico,
        ]);
    }
}
