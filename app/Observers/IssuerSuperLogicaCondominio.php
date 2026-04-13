<?php

namespace App\Observers;

use App\Models\Issuer;
use App\Models\SuperLogicaCondominio;

class IssuerSuperLogicaCondominio
{

    public function created(Issuer $issuer): void
    {
        $this->syncSuperLogicaCondominioId($issuer);

    }


    public function updated(Issuer $issuer): void
    {

        $this->syncSuperLogicaCondominioId($issuer);

    }

    private function syncSuperLogicaCondominioId(Issuer $issuer): void
    {
        $cnpj = sanitize($issuer->cnpj);

        if (empty($cnpj)) {
            return;
        }

        $condominioLocal = SuperLogicaCondominio::where('st_cpf_cond', $cnpj)
            ->orWhere('st_cpf_cond', $issuer->cnpj)
            ->first();

        if ($condominioLocal) {
            $issuer->superlogica_condominio_id = $condominioLocal->id_condominio_cond;
            $issuer->saveQuietly();
        }
    }

}
