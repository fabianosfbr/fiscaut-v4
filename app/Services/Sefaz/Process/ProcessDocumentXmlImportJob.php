<?php

namespace App\Jobs\Sefaz\Process;

use App\Services\Sefaz\CteService;
use App\Services\Sefaz\NfeService;
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
            $service = app(NfeService::class)->issuer($this->issuer);
            $service->exec($xmlReader, $xml, 'Importação');
        }
        // CTe
        if (isset($xmlReader['cteProc']['CTe'])) {
            $service = app(CteService::class)->issuer($this->issuer);
            $service->exec($xmlReader, $xml, 'Importação');
        }


    }
}
