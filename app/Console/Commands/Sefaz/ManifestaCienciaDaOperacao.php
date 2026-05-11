<?php

namespace App\Console\Commands\Sefaz;

use App\Jobs\Sefaz\ManifestaCienciaDaOperacaoSefazJob;
use App\Models\Issuer;
use Illuminate\Console\Command;

class ManifestaCienciaDaOperacao extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:manifesta-ciencia-operacao-nfe {--issuer= : ID do emitente para download específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dá ciência da opeação para nfe em todas os resumos de nfes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $issuerId = $this->option('issuer');


        $issuers = Issuer::whereNotNull('path_certificado')
            ->where('validade_certificado', '>', now())
            ->where('is_enabled', true)
            ->where('nfe_servico', true)
            ->when($issuerId !== null, fn($q) => $q->where('id', $issuerId))
            ->get();

        foreach ($issuers as $issuer) {

            ManifestaCienciaDaOperacaoSefazJob::dispatch($issuer)->onQueue('high');
        }
    }
}
