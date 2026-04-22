<?php

namespace App\Services\SuperLogica\Condominio;

use App\Services\SuperLogica\Connector\SuperLogicaConfig;

class SuperLogicaUnidadeConnector
{
    use SuperLogicaConfig;

    public function cadastrar(array $params = [])
    {
        return $this->post('/unidades/post', $params);
    }

    public function atualizar(array $params = [])
    {
        return $this->put('/unidades/post', $params);
    }

    public function listar(array $filter = [])
    {
        return $this->get('/unidades/index', $filter);
    }

    public function excluir(array $params = [])
    {
        return $this->post('unidades/delete', $params);
    }
}
