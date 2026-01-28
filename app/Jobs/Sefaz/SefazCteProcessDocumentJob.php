<?php

namespace App\Jobs\Sefaz;

use App\Models\Issuer;
use App\Models\XmlImportJob;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Models\LogSefazCteContent;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Xml\XmlCteReaderService;
use App\Services\Xml\XmlNfeReaderService;
use App\Services\Xml\XmlIdentifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SefazCteProcessDocumentJob implements ShouldQueue
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
        protected array $documento,
        protected Issuer $issuer,
        protected XmlImportJob $importJob
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Process the document based on its type
            switch ($this->documento['tipo_documento']) {
                case XmlIdentifierService::TIPO_CTE:
                    $this->processCteCompleta();
                    break;

                case XmlIdentifierService::TIPO_EVENTO_CTE:
                    $this->processEventoCte();
                    break;

                default:
                    Log::info('Tipo de documento não processado', [
                        'issuer_id' => $this->issuer->id,
                        'tipo' => $this->documento['tipo_documento'],
                        'nsu' => $this->documento['nsu'],
                    ]);

                    return;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar documento da SEFAZ', [
                'issuer_id' => $this->issuer->id,
                'nsu' => $this->documento['nsu'] ?? 'N/A',
                'tipo' => $this->documento['tipo_documento'] ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            $this->importJob->addError($e->getMessage());

            throw $e;
        }
    }

    /**
     * Processa NFe completa
     */
    private function processCteCompleta(): void
    {
        (new XmlCteReaderService)
            ->loadXml($this->documento['xml_content'])
            ->setOrigem('SEFAZ')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->registerLogContent($this->issuer, $this->documento['nsu'], $this->documento['max_nsu'], $this->documento['xml_content']);
        $this->importJob->incrementNumDocuments();
    }


    /**
     * Processa evento de CTe
     */
    private function processEventoCte(): void
    {
        (new XmlCteReaderService)
            ->loadXml($this->documento['xml_content'])
            ->setOrigem('SEFAZ')
            ->setIssuer($this->issuer)
            ->parse()
            ->save();

        $this->registerLogContent($this->issuer, $this->documento['nsu'], $this->documento['max_nsu'], $this->documento['xml_content']);
        $this->importJob->incrementNumEvents();
    }

    /**
     * Processa todas consultas de CTe
     */
    public function registerLogContent($issuer, $numnsu, $maxNSU, $xml)
    {
        $logContent = LogSefazCteContent::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
            ],
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
                'max_nsu' => $maxNSU,
                'xml' => $xml,
            ]
        );

        Log::notice('CTe NSU consulta SEFAZ: ' . $numnsu . ' maxnsu: ' . $maxNSU . ' Emissor: ' . $issuer->razao_social);

        return $logContent;
    }
}
