<?php

namespace App\Jobs\Sieg;

use App\Models\Issuer;
use App\Models\User;
use App\Models\XmlImportJob;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiegConnect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Configurações da API SIEG
     */
    protected $apiUrl = 'https://api.sieg.com/BaixarXmlsV2';

    protected $take = 50;

    protected $skip = 0;

    protected XmlImportJob $importJob;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $tipoDocumento,
        protected string $tipoCnpj,
        protected string $dataInicial,
        protected string $dataFinal,
        protected int $issuerId,
        protected int $importJobId,
    ) {
        $this->onQueue('sieg');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Formatar as datas para o padrão da API
            // $dataInicial = Carbon::createFromFormat('Y-m-d', $this->dataInicial)->startOfDay()->format('Y-m-d\TH:i:s.\0\0\0\Z');
            // $dataFinal = Carbon::createFromFormat('Y-m-d', $this->dataFinal)->endOfDay()->format('Y-m-d\TH:i:s.\9\9\9\Z');

            $issuer = Issuer::with('tenant')->find($this->issuerId);
            $tenant = $issuer->tenant;

            if (! isset($tenant->sieg_key)) {
                throw new Exception('Chave de API SIEG não configurada para o tenant '.$tenant->name);
            }
            $cnpj = $issuer->cnpj;

            $this->importJob = XmlImportJob::find($this->importJobId);
            $totalDocumentos = 0;
            $temMaisResultados = true;
            $this->skip = 0;

            do {
                // Preparar o payload da requisição
                $payload = [
                    'XmlType' => (int) $this->tipoDocumento,
                    'Take' => $this->take,
                    'Skip' => $this->skip,
                    'DataEmissaoInicio' => $this->dataInicial,
                    'DataEmissaoFim' => $this->dataFinal,
                    'Downloadevent' => true,
                ];

                // Adicionar o CNPJ conforme o tipo selecionado
                switch ($this->tipoCnpj) {
                    case 'emitente':
                        $payload['CnpjEmit'] = $cnpj;
                        break;
                    case 'destinatario':
                        $payload['CnpjDest'] = $cnpj;
                        break;
                    case 'tomador':
                        $payload['CnpjToma'] = $cnpj;
                        break;
                    case 'remetente':
                        $payload['CnpjReme'] = $cnpj;
                        break;
                }

                // Realizar a requisição para a API
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post($this->apiUrl.'?api_key='.$tenant->sieg_key, $payload);

                // Verificar se a requisição foi bem-sucedida
                if ($response->successful()) {
                    $responseData = $response->json();

                    if (isset($responseData['xmls']) && is_array($responseData['xmls'])) {
                        $resultados = $responseData['xmls'];
                        $totalDocumentos += count($resultados);

                        // Verifica se retornou o número máximo de resultados, indicando que pode haver mais
                        if (count($resultados) == $payload['Take']) {
                            $this->skip += $payload['Take'];
                            $temMaisResultados = true;
                        } else {
                            // Se retornou menos que o máximo, não há mais resultados
                            $temMaisResultados = false;
                        }

                        // Processar os XMLs retornados
                        $this->processarXmls($resultados, $this->importJob);
                    } else {
                        $this->enviarNotificacao(
                            'Atenção',
                            'Nenhum documento encontrado para os critérios informados.',
                            'warning'
                        );
                        $temMaisResultados = false;
                    }
                } else {
                    // Tratar erros da API
                    $errorMessage = 'Erro ao consultar a API do SIEG';

                    if ($response->status() === 404) {
                        $errorMessage = 'Nenhum arquivo XML localizado.';

                        $this->importJob->updateQuietly(['status' => XmlImportJob::STATUS_COMPLETED]);
                        $this->enviarNotificacao('Consulta Finalizada', $errorMessage, 'danger');
                    } else {
                        $responseData = $response->json();
                        if (is_array($responseData) && ! empty($responseData[0])) {
                            $errorMessage = $responseData[0];
                        }
                        $this->importJob->addError($errorMessage);
                        $this->importJob->updateQuietly(['status' => XmlImportJob::STATUS_FAILED]);
                        $this->enviarNotificacao('Erro', $errorMessage, 'danger');
                    }

                    // Interrompe o loop em caso de erro
                    $temMaisResultados = false;
                }

                // Aguarda um breve intervalo para não sobrecarregar a API
                // (limite de 30 requisições por minuto)
                usleep(300000); // 300ms
            } while ($temMaisResultados);

            Log::info('Importação SIEG concluída. Total de documentos: '.$totalDocumentos);
        } catch (Exception $e) {
            Log::error('Erro na importação SIEG: '.$e->getMessage());

            if (isset($this->importJob)) {
                $this->importJob->addError('Erro na importação: '.$e->getMessage());
                $this->importJob->updateQuietly(['status' => XmlImportJob::STATUS_FAILED]);
            }

            $this->enviarNotificacao(
                'Erro',
                'Ocorreu um erro ao processar a requisição: '.$e->getMessage(),
                'danger'
            );
        }
    }

    /**
     * Processa os XMLs retornados pela API em lote
     */
    protected function processarXmls(array $xmls, XmlImportJob $importJob): void
    {
        // Atualiza o total de arquivos no job de importação
        $totalAtual = $importJob->total_files ?? 0;
        $novoTotal = $totalAtual + count($xmls);

        $issuer = Issuer::find($this->issuerId);

        $importJob->updateQuietly([
            'total_files' => $novoTotal,
            'status' => XmlImportJob::STATUS_PROCESSING,
        ]);

        // Cria um job de processamento em lote para todos os XMLs
        ProcessXmlSiegBatch::dispatch($xmls, $importJob, $issuer);

        // Registra no log o início do processamento
        Log::info('Iniciado processamento em lote de '.count($xmls).' documentos XML do SIEG');
    }

    /**
     * Envia uma notificação para o usuário
     *
     * @param  string  $tipo  success|warning|danger|info
     */
    protected function enviarNotificacao(string $titulo, string $mensagem, string $tipo = 'info'): void
    {
        // Se temos um job de importação, usamos um dispatch separado para evitar problemas de serialização
        if (isset($this->importJob)) {
            $jobId = $this->importJob->id;
            $userId = $this->importJob->user_id;

            // Dispatch um job separado para enviar a notificação
            dispatch(function () use ($titulo, $mensagem, $tipo, $jobId, $userId) {
                $user = User::find($userId);
                if (! $user) {
                    return;
                }

                $notification = Notification::make()
                    ->title($titulo)
                    ->body($mensagem);

                switch ($tipo) {
                    case 'success':
                        $notification->success();
                        break;
                    case 'warning':
                        $notification->warning();
                        break;
                    case 'danger':
                        $notification->danger();
                        break;
                    default:
                        $notification->info();
                }

                // Criar a ação separadamente para evitar problemas de serialização
                $action = Action::make('view')
                    ->label('Ver detalhes')
                    ->url(route('filament.admin.resources.xml-import-history.index', ['record' => $jobId]));

                $notification->actions([$action]);

                $notification->sendToDatabase($user);
                $notification->send();
            });
        } else {
            // Caso não tenhamos um job de importação, enviamos a notificação diretamente
            $notification = Notification::make()
                ->title($titulo)
                ->body($mensagem);

            switch ($tipo) {
                case 'success':
                    $notification->success();
                    break;
                case 'warning':
                    $notification->warning();
                    break;
                case 'danger':
                    $notification->danger();
                    break;
                default:
                    $notification->info();
            }

            $notification->send();
        }
    }
}
