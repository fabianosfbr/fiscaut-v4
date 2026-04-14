<?php

namespace App\Console\Commands\Superlogica;

use App\Models\Issuer;
use App\Models\SuperLogicaCondominio;
use App\Models\SuperLogicaContaBancaria;
use App\Models\SuperLogicaFornecedor;
use App\Models\SuperLogicaPlanoDeConta;
use App\Models\SuperLogicaUnidade;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CondominioSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:condominio-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $issuer = Issuer::find(63);

        if (!isset($issuer->superlogica_condominio_id)) {
            $this->error('Empresa não vinculada ao condomínio no Superlogica.');
            return;
        }

        //$this->syncCondominio($issuer);

        // $this->info('Condominios sincronizados com sucesso.');

        $this->syncUnidade($issuer);
        $this->info('Unidades sincronizadas com sucesso.');

        $this->syncFornecedor($issuer);
        $this->info('Fornecedores sincronizados com sucesso.');


        $this->syncContaBancaria($issuer);
        $this->info('Contas bancárias sincronizadas com sucesso.');


        $this->syncPlanoDeContas($issuer);
        $this->info('Plano de contas sincronizadas com sucesso.');



    }

    private function syncCondominio(Issuer $issuer)
    {
        $service = new \App\Services\SuperlogicaConnectionService($issuer);

        $havePagination = true;
        $pagina = 1;
        while ($havePagination) {
            $condominios = $service->condominio()->listar([
                'id' => -1,
                'somenteCondominiosAtivos' => 1,
                'itensPorPagina' => 50,
                'pagina' => $pagina,
            ]);

            if (count($condominios) == 0) {
                $havePagination = false;
            }

            $pagina++;

            foreach ($condominios as $condominio) {

                SuperLogicaCondominio::updateOrCreate([
                    'id_condominio_cond' => $condominio['id_condominio_cond'],
                    'st_cpf_cond' => sanitize($condominio['st_cpf_cond']),
                ], [
                    'metadados' => $condominio,
                ]);

            }

        }

    }

    private function syncUnidade(Issuer $issuer)
    {
        $service = new \App\Services\SuperlogicaConnectionService($issuer);

        $condominios = SuperLogicaCondominio::where('id_condominio_cond', $issuer->superlogica_condominio_id)->get();

        foreach ($condominios as $condominio) {

            $havePagination = true;
            $pagina = 1;
            while ($havePagination) {
                $unidades = $service->unidade()->listar([
                    'idCondominio' => $condominio->id_condominio_cond,
                    'exibirDadosDosContatos' => 1,
                    'exibirGruposDasUnidades' => 1,
                    'exibirInadimplencia' => 1,
                    'itensPorPagina' => 50,
                    'pagina' => $pagina,
                ]);

                if (count($unidades) == 0) {
                    $havePagination = false;
                }

                $pagina++;

                foreach ($unidades as $unidade) {

                    SuperLogicaUnidade::updateOrCreate([
                        'id_condominio' => $condominio->id_condominio_cond,
                        'id_unidade_uni' => $unidade['id_unidade_uni'],
                    ], [
                        'metadados' => $unidade,
                    ]);

                }
            }

        }
    }

    private function syncContaBancaria(Issuer $issuer)
    {
        $service = new \App\Services\SuperlogicaConnectionService($issuer);

        $condominios = SuperLogicaCondominio::where('id_condominio_cond', $issuer->superlogica_condominio_id)->get();


        foreach ($condominios as $condominio) {

            $havePagination = true;
            $pagina = 1;
            while ($havePagination) {
                $contas = $service->condominio()
                    ->contaBancaria()
                    ->listar([
                        'idCondominio' => $issuer->superlogica_condominio_id,
                        'exibirDadosAgencia' => 1,
                        'exibirContasFechadas' => 1,
                        'exibirDadosBanco' => 1,
                        'itensPorPagina' => 50,
                        'pagina' => $pagina,
                    ]);

                if (count($contas) == 0) {
                    $havePagination = false;
                }

                $pagina++;

                foreach ($contas as $conta) {

                    SuperLogicaContaBancaria::updateOrCreate([
                        'id_condominio' => $condominio->id_condominio_cond,
                        'st_conta_cb' => $conta['st_conta_cb'],
                    ], [
                        'metadados' => $conta,
                    ]);

                }
            }

        }
    }

    private function syncPlanoDeContas(Issuer $issuer)
    {
        $service = new \App\Services\SuperlogicaConnectionService($issuer);

        $condominios = SuperLogicaCondominio::where('id_condominio_cond', $issuer->superlogica_condominio_id)->get();

        foreach ($condominios as $condominio) {


            $havePagination = true;
            $pagina = 1;
            while ($havePagination) {
                $contas = $service->condominio()
                    ->planoDeConta()
                    ->listar([
                        'ID_CONDOMINIO_COND' => $issuer->superlogica_condominio_id,
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


    private function syncFornecedor(Issuer $issuer)
    {

        $service = new \App\Services\SuperlogicaConnectionService($issuer);

        $havePagination = true;
        $pagina = 1;
        while ($havePagination) {
            $fornecedores = $service
                ->despesa()
                ->listarFornecedor([
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
