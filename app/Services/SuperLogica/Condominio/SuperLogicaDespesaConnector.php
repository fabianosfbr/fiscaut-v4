<?php

namespace App\Services\SuperLogica\Condominio;


use App\Services\SuperLogica\Connector\SuperLogicaConfig;

class SuperLogicaDespesaConnector
{
    use SuperLogicaConfig;



    public function listar(array $filter = [])
    {
        return $this->get('/despesas/index', $filter);
    }

    public function listarFornecedor(array $filter = [])
    {
        return $this->get('/fornecedores/index', $filter);
    }

    

}
