<?php

namespace App\Jobs;

use Exception;
use Throwable;
use App\Models\Issuer;
use App\Events\CteCancelada;
use App\Events\NfeCancelada;
use App\Models\XmlImportJob;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Services\Sefaz\CteService;
use App\Services\Sefaz\NfeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Xml\XmlCteEventoService;
use App\Services\Xml\XmlCteReaderService;
use App\Services\Xml\XmlExtractorService;
use App\Services\Xml\XmlNfeEventoService;
use App\Services\Xml\XmlNfeReaderService;
use App\Services\Xml\XmlNfeResumoService;
use App\Services\Xml\XmlIdentifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessXmlFile implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Create a new job instance.
     */
    public function __construct(
        protected string $fileKey,
        protected XmlImportJob $importJob,
        protected Issuer $issuer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            // Verifica se o arquivo existe antes de tentar processá-lo
            if (! Storage::disk('local')->exists($this->fileKey)) {
                $mensagemErro = 'Erro ao processar arquivo: O arquivo não existe no caminho ' . $this->fileKey;
                $this->importJob->addError($mensagemErro);
                Log::error('Erro na importação de XML: ' . $mensagemErro);

                return;
            }

            $filePath = Storage::disk('local')->path($this->fileKey);
            $extractor = new XmlExtractorService;
            $xmlContents = $extractor->extractFromPath($filePath);

            foreach ($xmlContents as $xmlContent) {

                $xmlContent = $xmlContent['content'];

                $tipoXml = XmlIdentifierService::identificarTipoXml($xmlContent);

                $xmlReader = loadXmlReader($xmlContent);
               
                $serviceNfe = app(NfeService::class)->issuer($this->issuer);
                $serviceCte = app(CteService::class)->issuer($this->issuer);
              
                // Processar conforme o tipo
                switch ($tipoXml) {
                    case XmlIdentifierService::TIPO_NFE:

                        $serviceNfe->exec($xmlReader, $xmlContent, 'Importação');            

                        $this->importJob->incrementNumDocuments();
                        break;

                    case XmlIdentifierService::TIPO_NFE_RESUMO:

                        $serviceNfe->exec($xmlReader, $xmlContent, 'Importação');

                        $this->importJob->incrementNumEvents();
                        break;

                    // Processar evento de cancelamento de NF-e
                    case XmlIdentifierService::TIPO_EVENTO_NFE:

                        $serviceNfe->exec($xmlReader, $xmlContent, 'Importação');
                      
                        $this->importJob->incrementNumEvents();
                        break;

                    case XmlIdentifierService::TIPO_CTE:
                        $serviceCte->exec($xmlReader, $xmlContent, 'Importação');

                        $this->importJob->incrementNumDocuments();
                        break;

                    // Processar evento de CT-e
                    case XmlIdentifierService::TIPO_EVENTO_CTE:

                        $serviceCte->exec($xmlReader, $xmlContent, 'Importação');

                        // Disparar evento de cancelamento
                        // event(new CteCancelada($event));

                        $this->importJob->incrementNumEvents();
                        break;

                    default:
                        throw new Exception('Tipo de XML não suportado: ' . $tipoXml);
                }
            }



            $this->importJob->incrementImportedFiles();
        } catch (Exception $e) {
            $this->failed($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $mensagemErro = 'Falha no processamento do arquivo: ' . $exception->getMessage();
        $this->importJob->addError($mensagemErro);
        Log::error('Falha na importação de XML: ' . $mensagemErro);
    }
}
