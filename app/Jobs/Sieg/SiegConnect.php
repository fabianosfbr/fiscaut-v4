<?php

namespace App\Jobs\Sieg;

use App\Models\Issuer;
use App\Models\XmlImportJob;
use Exception;
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
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     * Using backoff array for exponential backoff: 60s, 120s, 240s, 480s, 960s
     *
     * @var array
     */
    public $backoff = [60, 120, 240, 480, 960];

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
        protected bool $event = true,
    ) {
        $this->onQueue('sieg');
    }

    /**
     * Mapeia tipo de documento SIEG para a classe de job correspondente.
     */
    protected function getJobClass(int $tipoDocumento): string
    {
        return match ($tipoDocumento) {
            1 => ProcessDocumentNfeSiegJob::class,
            2 => ProcessDocumentCteSiegJob::class,
            3 => ProcessDocumentNfseSiegJob::class,
            4 => ProcessDocumentNfceSiegJob::class,
            default => throw new Exception('Tipo de documento SIEG inválido: '.$tipoDocumento),
        };
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $issuer = Issuer::with('tenant')->find($this->issuerId);
            $tenant = $issuer->tenant;

            if (! isset($tenant->sieg_key)) {
                throw new Exception('Chave de API SIEG não configurada para o tenant '.$tenant->name);
            }
            $cnpj = $issuer->cnpj;

            $castXmlType = [
                '1' => 'NFe',
                '2' => 'CTe',
                '3' => 'NFS-e',
                '4' => 'NFCe',
                '5' => 'CF-e',
            ];

            $this->importJob = XmlImportJob::find($this->importJobId);
            $totalDocumentos = 0;
            $temMaisResultados = true;
            $this->skip = 0;

            $jobClass = $this->getJobClass($this->tipoDocumento);

            do {
                // Preparar o payload da requisição
                $payload = [
                    'XmlType' => (int) $this->tipoDocumento,
                    $this->tipoCnpj => $cnpj,
                    'Take' => $this->take,
                    'Skip' => $this->skip,
                    'DataEmissaoInicio' => $this->dataInicial,
                    'DataEmissaoFim' => $this->dataFinal,
                    'Downloadevent' => $this->event,
                ];

                // Realizar a requisição para a API
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                    ->timeout(120)  // 2 minutos para timeout da requisição
                    ->connectTimeout(30)  // 30 segundos para timeout de conexão
                    ->post($this->apiUrl.'?api_key='.$tenant->sieg_key, $payload);

                // Verificar se a requisição foi bem-sucedida
                if ($response->successful()) {
                    $responseData = $response->json();
                    $totalDocumentosPagina = count($responseData ?? []);
                   

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

                        // Atualiza o total de arquivos no import job e dispatches um job por documento
                        $this->importJob->updateQuietly([
                            'total_files' => $totalDocumentos,
                            'status' => XmlImportJob::STATUS_PROCESSING,
                        ]);

                        foreach ($resultados as $value) {
                            $xml = base64_decode($value);

                            $jobClass::dispatch($xml, $issuer, $this->importJob);
                        }
                       
                    } else {
                        $this->importJob->updateQuietly([
                            'total_files' => $totalDocumentos,
                            'status' => XmlImportJob::STATUS_COMPLETED,
                        ]);
                        $temMaisResultados = false;
                    }
                } else {
                    // Tratar erros da API
                    $errorMessage = 'Erro ao consultar a API do SIEG';
                    $statusCode = $response->status();

                    if ($statusCode === 404) {
                        $this->importJob->updateQuietly(['status' => XmlImportJob::STATUS_COMPLETED]);
                        $temMaisResultados = false;
                    } elseif ($statusCode === 429) {
                        // Rate limit excedido - respeitar Retry-After ou usar backoff exponencial do job
                        $retryAfter = $response->header('Retry-After');
                        $waitSeconds = $retryAfter ? (int) $retryAfter : 60;
                        
                        Log::channel('sieg_log')->warning("Rate limit SIEG (429). Aguardando {$waitSeconds}s antes de retry. Skip: {$this->skip}");
                        
                        // Atualiza job para indicar aguardando retry
                        $this->importJob->updateQuietly([
                            'status' => XmlImportJob::STATUS_PROCESSING,
                            'error_message' => "Rate limit atingido. Retentativa em {$waitSeconds}s",
                        ]);
                        
                        // Lança exceção para triggerar o retry do job com backoff exponencial
                        throw new Exception("Rate limit SIEG (429). Retry-After: {$waitSeconds}s");
                    } else {
                        $responseData = $response->json();
                        Log::channel('sieg_log')->error('Erro na consulta do SIEG: '.$errorMessage . ' - Status: '.$statusCode . ' - Skip: '.$this->skip);
                        if (is_array($responseData) && ! empty($responseData[0])) {
                            $errorMessage = $responseData[0];
                        }
                        $this->importJob->addError($errorMessage);
                        $this->importJob->updateQuietly(['status' => XmlImportJob::STATUS_FAILED]);
                        $temMaisResultados = false;
                    }
                }

                // Aguarda intervalo para respeitar limite de 30 requisições por minuto (2 segundos)
                usleep(2000000);  // 2000ms = 2 segundos
            } while ($temMaisResultados);

            $this->importJob->updateQuietly([
                'total_files' => $totalDocumentos,
                'status' => XmlImportJob::STATUS_COMPLETED,
            ]);
            Log::channel('sieg_log')->info('Importação SIEG concluída. Tipo consulta: '.$this->tipoCnpj . ' Total de documentos: '.$totalDocumentos . ' Empresa: '.$issuer->razao_social);
            
        } catch (Exception $e) {
            Log::channel('sieg_log')->error('Erro na importação SIEG: '.$e->getMessage());

            if (isset($this->importJob)) {
                $this->importJob->addError('Erro na importação: '.$e->getMessage());
                $this->importJob->updateQuietly(['status' => XmlImportJob::STATUS_FAILED]);
            }
        }
    }
}
