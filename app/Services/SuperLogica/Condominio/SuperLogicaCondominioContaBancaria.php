<?php

namespace App\Services\SuperLogica\Condominio;

use App\Services\SuperLogica\Connector\SuperLogicaConfig;

class SuperLogicaCondominioContaBancaria
{

     use SuperLogicaConfig;



    public function listar(array $filter = [])
    {
        return $this->get('/contabancos/index', $filter);
    }


}
