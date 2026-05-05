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
}
