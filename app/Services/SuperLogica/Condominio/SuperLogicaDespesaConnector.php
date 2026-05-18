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

    public function listarDespesa(array $filter = [])
    {
        return $this->get('/despesas/index', $filter);
    }

    public function listarFavorecido(array $filter = [])
    {
        return $this->get('/fornecedores/index', $filter);
    }

    public function listarDadosPagamentoFavorecido(array $filter = [])
    {
        return $this->get('/contatofavorecido/index', $filter);
    }
}
