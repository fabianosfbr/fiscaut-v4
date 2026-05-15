<?php

namespace App\Services\SuperLogica\Condominio;

use App\Services\SuperLogica\Connector\SuperLogicaConfig;

class SuperLogicaReceitaConnector
{
    use SuperLogicaConfig;

    public function listarInadimplencia(array $filter = [])
    {
        return $this->get('/inadimplencia/index', $filter);
    }

    public function listarProcessosJudiciais(array $filter = [])
    {
        return $this->get('/processos', $filter);
    }

    public function novoProcessoJudicial(array $params = [])
    {
        return $this->postForm('/processos', $params);
    }

    public function listarCobranca(array $filter = [])
    {
        return $this->get('/cobranca/index', $filter);
    }
}
