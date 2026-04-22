<?php

namespace App\Services\SuperLogica\Condominio;

use App\Services\SuperLogica\Connector\SuperLogicaConfig;

class SuperLogicaCondominioConnector
{
    use SuperLogicaConfig;

    public function cadastrar(array $params = [])
    {
        return $this->post('/condominios', $params);
    }

    public function atualizar(array $params = [])
    {
        return $this->put('/condominios', $params);
    }

    public function listar(array $filter = [])
    {
        return $this->get('/condominios/get', $filter);
    }

    public function contaBancaria()
    {
        return new SuperLogicaCondominioContaBancariaConnector($this->issuer);
    }

    public function planoDeConta()
    {
        return new SuperLogicaCondominioPlanoDeContaConnector($this->issuer);
    }
}
