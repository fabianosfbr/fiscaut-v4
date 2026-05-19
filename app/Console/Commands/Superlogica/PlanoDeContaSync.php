<?php

namespace App\Console\Commands\Superlogica;

use App\Models\Issuer;
use App\Models\SuperLogicaCondominio;
use App\Models\SuperLogicaContaBancaria;
use App\Models\SuperLogicaFornecedor;
use App\Models\SuperLogicaPlanoDeConta;
use App\Models\SuperLogicaUnidade;
use App\Models\Tenant;
use App\Services\SuperlogicaConnectionService;
use Illuminate\Console\Command;

class PlanoDeContaSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:plano-de-conta-sync {--tenant= : ID do emitente para download específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza os planos do Superlogica com o banco de dados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');

        $tenants = Tenant::whereNotNull('superlogica_base_url')
            ->whereNotNull('superlogica_app_token')
            ->whereNotNull('superlogica_access_token')
            ->when($tenantId !== null, fn($q) => $q->where('id', $tenantId))
            ->get();

        foreach ($tenants as $tenant) {
            $this->syncPlanoDeContas($tenant);

            $this->info('Planos de conta sincronizados com sucesso.');
        }

        // $this->info('Plano de contas sincronizadas com sucesso.');
    }

    private function syncPlanoDeContas(Tenant $tenant)
    {
        $service = new SuperlogicaConnectionService($tenant);

        $condominios = SuperLogicaCondominio::where('tenant_id', $tenant->id)->get();

        foreach ($condominios as $condominio) {
            $havePagination = true;
            $pagina = 1;
            while ($havePagination) {
                $contas = $service->planoDeContas()->listar([
                    'ID_CONDOMINIO_COND' => $condominio->id_condominio_cond,
                    'itensPorPagina' => 50,
                    'pagina' => $pagina,
                ]);

                if (count($contas) == 0) {
                    $havePagination = false;
                }

                $pagina++;

                foreach ($contas as $conta) {
                    SuperLogicaPlanoDeConta::updateOrCreate([
                        'id_condominio' => $condominio->id_condominio_cond,
                        'st_conta_cont' => $conta['st_conta_cont'],
                        'st_descricao_cont' => $conta['st_descricao_cont'],
                        'st_ordenacao_cont' => $conta['st_ordenacao_cont'],
                    ], [
                        'metadados' => $conta,
                    ]);
                }
            }
        }
    }

    private function syncFornecedor(Tenant $tenant)
    {
        $service = new SuperlogicaConnectionService($tenant);

        $havePagination = true;
        $pagina = 1;
        while ($havePagination) {
            $fornecedores = $service->despesa()->listarFavorecido([
                'contatosDoTipo' => 'fornecedores',
                'itensPorPagina' => 50,
                'pagina' => $pagina,
            ]);

            if (count($fornecedores) == 0) {
                $havePagination = false;
            }

            $pagina++;

            foreach ($fornecedores as $fornecedor) {
                SuperLogicaFornecedor::updateOrCreate([
                    'id_contato_con' => $fornecedor['id_contato_con'],
                    'id_condominio' => $fornecedor['id_condominio_cond'],
                    'st_nome_con' => $fornecedor['st_nome_con'],
                    'st_cpf_con' => $fornecedor['st_cpf_con'] ?? null,
                ], [
                    'metadados' => $fornecedor,
                ]);
            }
        }
    }
}
