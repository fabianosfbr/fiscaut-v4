<?php

namespace App\Jobs\Sefaz\Process;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessResponseCteSefazJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $response;

    public $issuer;

    public $origem;

    public function __construct($issuer, $response, $origem)
    {
        $this->onQueue('sefaz');
        $this->response = $response;
        $this->issuer = $issuer;
        $this->origem = $origem;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reader = loadXmlReader($this->response);

        $root = $reader['retDistDFeInt'] ?? [];
        $maxNSU = $root['maxNSU'] ?? null;
        $docZipList = xml_list($root['loteDistDFeInt']['docZip'] ?? null);

        foreach ($docZipList as $key => $doc) {

            // Cada doc vira um job de processamento
            ProcessXmlResponseCteSefazJob::dispatch($this->issuer, $this->response, $key, $this->origem, $maxNSU)
                ->onQueue('sefaz');
        }
    }
}
