<?php

namespace App\Jobs\Sefaz\Process;

use App\Services\Sefaz\Traits\HasLogSefaz;
use App\Services\Xml\XmlCteReaderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessXmlResponseCteSefazJob implements ShouldQueue
{
    use Dispatchable, HasLogSefaz, InteractsWithQueue, Queueable, SerializesModels;

    public $response;

    public $key;

    public $issuer;

    public $origem;

    public $maxNSU;

    public function __construct($issuer, $response, $key, $origem, $maxNSU)
    {
        $this->response = $response;
        $this->key = $key;
        $this->issuer = $issuer;
        $this->origem = $origem;
        $this->maxNSU = $maxNSU;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reader = loadXmlReader($this->response);

        $root = $reader['retDistDFeInt'] ?? [];
        $docZipList = xml_list($root['loteDistDFeInt']['docZip'] ?? null);
        $docZip = $docZipList[$this->key] ?? null;

        if (! is_array($docZip)) {
            return;
        }

        $numnsu = intval($docZip['@attributes']['NSU'] ?? 0);
        $content = $docZip['@content'] ?? '';
        $xml = gzdecode(base64_decode($content));

        if ($xml === false) {
            return;
        }

        $xmlReader = loadXmlReader($xml);

        $this->registerLogCteContent($this->issuer, $numnsu, $this->maxNSU, $xml);

        app(XmlCteReaderService::class)
            ->loadXml($xml)
            ->setIssuer($this->issuer)
            ->setOrigem($this->origem)
            ->parse()
            ->save();
    }
}
