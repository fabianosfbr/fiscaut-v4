<?php

namespace App\Services\SuperLogica\Condominio;

use App\Services\SuperLogica\Connector\SuperLogicaConfig;

class SuperLogicaDocumentoConnector
{
    use SuperLogicaConfig;

    public function listar(array $filter = [])
    {
        return $this->get('/impressoes/index', $filter);
    }

    public function download(array $filter = [])
    {
       
        return $this->getFile('/publico/downloadarquivo', $filter);
    }    
}
