<?php

namespace App\Jobs\Sieg;

use App\Models\Issuer;
use App\Models\XmlImportJob;
use App\Services\Xml\XmlCteReaderService;
use App\Services\Xml\XmlIdentifierService;
use App\Services\Xml\XmlNfeReaderService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessXmlSieg implements ShouldQueue
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
        protected string $xmlContent,
        protected XmlImportJob $importJob,
        protected Issuer $issuer
    ) {
        $this->onQueue('sieg');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Identificar tipo de XML
            $tipoXml = XmlIdentifierService::identificarTipoXml($this->xmlContent);

            // Processar conforme o tipo
            switch ($tipoXml) {
                case XmlIdentifierService::TIPO_NFE:
                    $this->processNfeCompleta();
                    break;

                    // Processar eventos de NF-e
                case XmlIdentifierService::TIPO_EVENTO_NFE:
                    $this->processEventoNfe();
                    break;

                case XmlIdentifierService::TIPO_NFE_RESUMO:
                    $this->processResumoNfe();
                    break;

                case XmlIdentifierService::TIPO_CTE:
                    $this->processCteCompleta();
                    break;

                    // Processar eventos de CT-e
                case XmlIdentifierService::TIPO_EVENTO_CTE:
                    $this->processEventoCte();
                    break;

                default:
                    Log::info('Tipo de documento não processado SIEG', [
                        'issuer_id' => $this->issuer->id,
                        'tipo' => $tipoXml,
                    ]);

                    return;
            }

        } catch (\Exception $e) {
            Log::error('Erro ao processar documento SIEG', [
                'issuer_id' => $this->issuer->id,
                'tipo' => $tipoXml ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            $this->importJob->addError($e->getMessage());

            throw $e;
        }
    }

    private function processNfeCompleta(): void
    {
        (new XmlNfeReaderService)
            ->loadXml($this->xmlContent)
            ->setOrigem('SIEG')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->importJob->incrementNumDocuments();
    }

    /**
     * Processa resumo de NFe
     */
    private function processResumoNfe(): void
    {
        (new XmlNfeReaderService)
            ->loadXml($this->xmlContent)
            ->setOrigem('SIEG')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->importJob->incrementNumEvents();
    }

    /**
     * Processa evento de NFe
     */
    private function processEventoNfe(): void
    {
        (new XmlNfeReaderService)
            ->loadXml($this->xmlContent)
            ->setOrigem('SIEG')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->importJob->incrementNumEvents();
    }

    private function processCteCompleta(): void
    {
        (new XmlCteReaderService)
            ->loadXml($this->xmlContent)
            ->setOrigem('SIEG')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->importJob->incrementNumDocuments();
    }

    /**
     * Processa evento de CTe
     */
    private function processEventoCte(): void
    {
        (new XmlCteReaderService)
            ->loadXml($this->xmlContent)
            ->setOrigem('SIEG')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->importJob->incrementNumEvents();
    }
}
