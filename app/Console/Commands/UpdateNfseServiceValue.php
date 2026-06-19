<?php

namespace App\Console\Commands;

use App\Models\NotaFiscalServico;
use Illuminate\Console\Command;

class UpdateNfseServiceValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-nfse-service-value';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o valor do serviço das notas fiscais de serviço dos últimos 60 dias para o vBC do XML.';

    public function handle(): int
    {
        $this->info('Iniciando a atualização dos valores de serviço das NFS-e...');

        // Obter notas fiscais de serviço dos últimos 60 dias
        $nfs = NotaFiscalServico::where('data_emissao', '>=', now()->subDays(20))
            ->where('valor_servico', 0)
            ->chunkById(500, function ($nfes): void {
                foreach ($nfes as $nfe) {

                    $xmlContent = $nfe->xml;

                    $xmlObj = simplexml_load_string($xmlContent);

                    // Verifica se o XML foi carregado e se a estrutura vBC existe
                    if ($xmlObj) {
                        $vBC = (float) ($xmlObj->infNFSe->valores->vBC ?? 0);
                        $vLiq = (float) ($xmlObj->infNFSe->valores->vLiq ?? 0);
                        $nfe->valor_servico = $vBC > 0 ? $vBC : $vLiq;
                        $nfe->save();
                        $this->comment("NFSe ID: {$nfe->id} atualizada com valor_servico: {$nfe->valor_servico}");
                    } else {
                        $this->warn("Não foi possível extrair ValorServicos do XML para a NFSe ID: {$nfe->id}");
                    }
                }
            });

        $this->info('Atualização concluída.');

        return Command::SUCCESS;
    }
}
