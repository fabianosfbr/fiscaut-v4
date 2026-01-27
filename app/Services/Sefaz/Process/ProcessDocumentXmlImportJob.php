<?php

namespace App\Jobs\Sefaz\Process;

use App\Services\Xml\XmlCteReaderService;
use App\Services\Xml\XmlNfeReaderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentXmlImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = false;

    public $timeout = 120000;

    public $content;

    public $issuer;

    public function __construct($content, $issuer)
    {
        $this->content = $content;
        $this->issuer = $issuer;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $xml = $this->content;
        $xmlReader = loadXmlReader($xml);

        // NFe
        if (isset($xmlReader['nfeProc']['NFe']) || isset($xmlReader['procEventoNFe'])) {
            app(XmlNfeReaderService::class)
                ->loadXml($xml)
                ->setIssuer($this->issuer)
                ->setOrigem('IMPORTADO')
                ->parse()
                ->save();
        }
        // CTe
        if (isset($xmlReader['cteProc']['CTe'])) {
            app(XmlCteReaderService::class)
                ->loadXml($xml)
                ->setIssuer($this->issuer)
                ->setOrigem('IMPORTADO')
                ->parse()
                ->save();
        }


    }
}
