<?php

namespace App\Services\SuperLogica\Condominio;

use App\Services\SuperLogica\Connector\SuperLogicaConfig;

class SuperLogicaArquivoConnector
{
    use SuperLogicaConfig;

    public function cadastrar(array $file, array $params)
    {
        return $this->attach('/arquivos/', $file, $params);
    }
}
